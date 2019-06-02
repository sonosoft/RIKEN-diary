<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/project/save_action.php
 */


class AdminProjectSaveAction extends AdminProjectController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Project', 'ProjectDiary', 'ProjectMail');
    $this->useValidator('ProjectForm');

    /* トランザクション */
    $this->db->begin();

    /**/
    try{
      /* 検証 */
      $data = $this->ProjectFormValidator->validate($this->app->readRequest('project', array()));

      /* 保存 */
      if($data['id'] !== null){
	if(($project = $this->ProjectModel->findById($data['id'])) === null){
	  $this->redirect('default:admin/error.invalid_access');
	}
	$project->setAttributes($data);
      }else{
	$project = $this->ProjectModel->newModel($data);
	$project->token = $this->ProjectModel->generateToken();
	$project->status = STATUS_ENABLED;
	$project->created_at = $this->app->data['_now_'];
      }
      $project->updated_at = $this->app->data['_now_'];
      $project->save();
      /**/
      $list = $this->ProjectDiaryModel->all(array('where'=>'[project_id] = :project_id'), array('project_id'=>$project->id));
      $ids = array();
      foreach($data['diaries'] as $diary){
	$found = false;
	foreach($list as $entry){
	  if($entry->diary_id == $diary['id']){
	    $ids[] = $entry->id;
	    $found = true;
	    break;
	  }
	}
	if($found === false){
	  $pd = $this->ProjectDiaryModel->newModel();
	  $pd->project_id = $project->id;
	  $pd->diary_id = $diary['id'];
	  $pd->save();
	  $ids[] = $pd->id;
	}
      }
      $this->db->query(
	'DELETE FROM project_diary WHERE id NOT IN :ids AND project_id = :project_id',
	array('ids'=>$ids, 'project_id'=>$project->id)
      );
      /**/
      $list = $this->ProjectMailModel->all(array('where'=>'[project_id] = :project_id'), array('project_id'=>$project->id));
      $ids = array();
      foreach($data['mails'] as $mail){
	$found = false;
	foreach($list as $entry){
	  if($entry->mail_id == $mail['id']){
	    $ids[] = $entry->id;
	    $found = true;
	    break;
	  }
	}
	if($found === false){
	  $pd = $this->ProjectMail->newModel();
	  $pd->project_id = $project->id;
	  $pd->mail_id = $mail['id'];
	  $pd->save();
	  $ids[] = $pd->id;
	}
      }
      $this->db->query(
	'DELETE FROM project_mail WHERE id NOT IN :ids AND project_id = :project_id',
	array('ids'=>$ids, 'project_id'=>$project->id)
      );

      /* コミット */
      $this->db->commit();
    }catch(Exception $e){
      /* ロールバック */
      $this->db->rollback();

      /**/
      if($e instanceof Eln_Validation){
	/* エラー */
	$this->app->exportValidation($e, 'project');
	return $this->viewForm();
      }else if($e instanceof Eln_Redirection){
	/* リダイレクト */
	throw $e;
      }else{
	/* 例外 */
	$this->app->writeLog('admin/project/save', $e->getMessage());
	$this->app->writeLog('admin/project/save', print_r(Eln_Database::$logs, true));
	$this->redirect('default:admin/error.unexpected');
      }
    }

    /**/
    $this->redirect('default:admin/project.search');
  }
}
