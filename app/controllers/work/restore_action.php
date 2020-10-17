<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * work/restore_action.php
 */


class WorkRestoreAction extends Controller {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('User', 'Project', 'ProjectUser', 'ProjectDiary', 'Visit', 'Restoration', 'Page');
    
    /* セッション初期化 */
    $this->app->removeSession('work_data');
    $this->app->data['work_data'] = array();
    
    /* URL */
    if(strcmp($this->app->route['route'], 'restoration') == 0){
      /**/
      $this->db->begin();
      try{
	/* トークン */
	if(isset($this->app->route['token']) === false){
	  $this->db->rollback();
	  return 'work/error/invalid_url';
	}
	
	/* プロジェクト・ユーザ */
	$restoration = $this->RestorationModel->one(
	  array('joins'=>array('project', 'user'), 'where'=>'[token] = :token AND [status] = :enabled'),
	  array('token'=>$this->app->route['token'], 'enabled'=>STATUS_ENABLED)
	);
	if($restoration === null){
	  $this->db->rollback();
	  return 'work/error/invalid_url';
	}
	if(($user = $restoration->user) === null || ($project = $restoration->project) === null){
	  $this->db->rollback();
	  return 'work/error/invalid_url';
	}
	$link = $this->ProjectUserModel->one(
	  array('where'=>'[project_id] = :project_id AND [user_id] = :user_id'),
	  array('project_id'=>$project->id, 'user_id'=>$user->id)
	);
	if($link === null){
	  $this->db->rollback();
	  return 'work/error/invalid_url';
	}
	if($project->from_date->compare($restoration->record) > 0 || $project->to_date->compare($restoration->record) < 0){
	  $this->db->rollback();
	  return 'work/error/out_of_date';
	}
	/**/
	$this->app->data['user'] = $user;
	$this->app->data['project'] = $project;
	
	/* 日誌 */
	switch($restoration->timing){
	case TIMING_GETUP:
	  $time = '06:00';
	  break;
	case TIMING_AM:
	  $time = '13:00';
	  break;
	case TIMING_PM:
	  $time = '19:00';
	  break;
	case TIMING_GOTOBED:
	  $time = '21:00';
	  break;
	}
	$datetime = new Eln_Date(strtotime($restoration->record->format('%Y/%m/%d').' '.$time));
	$diaries = array();
	foreach($this->ProjectDiaryModel->getByProject($project->id) as $entry){
	  if($entry->diary->isActive($datetime)){
	    if(!$entry->diary->separated){
	      $diaries[] = $entry->diary;
	    }
	  }
	}
	if(empty($diaries)){
	  $this->db->rollback();
	  return 'work/error/out_of_time';
	}
	if(($pages = $this->PageModel->load($diaries)) === null){
	  $this->app->writeLog('work/restore #1', 'failed to read data file.');
	  $this->redirect('default:work.error');
	}
	$indexes = $this->PageModel->collectIndexes($pages);
	if(empty($indexes)){
	  $this->app->writeLog('work/restore #2', 'failed to get page indexes.');
	  $this->redirect('default:work.error');
	}
	$this->app->data['diaries'] = $diaries;
	$this->app->data['numPages'] = count($indexes);

	/* 訪問 */
	$codes = array();
	foreach($diaries as $diary){
	  $codes[] = 'DY'.$diary->code;
	}
	$visit = $this->VisitModel->newModel();
	$visit->user_id = $user->id;
	$visit->project_id = $project->id;
	$visit->restoration_id = $restoration->id;
	if($code !== null){
	  $visit->diary_id = $diaries[0]->id;
	}
	$visit->diaries = implode(',', $codes);
	$visit->visited_on = $restoration->record;
	$visit->timing = $restoration->timing;
	$visit->page = $indexes[0];
	$visit->status = STATUS_STARTED;
	$visit->started_at = $this->app->data['_now_'];
	$visit->save();
	
	/* コミット */
	$this->db->commit();
	
	/* セッション */
	$this->app->data['visit'] = $visit;
	$this->app->storeSession('work_data.visit_id', $visit->id);
      }catch(Exception $e){
	/* ロールバック */
	$this->db->rollback();
	
	/* エラー */
	$this->app->writeLog('work/restore', $e->getMessage());
	$this->redirect('default:work.error');
      }
            
      /**/
      return 'work/index';
    }
    
    /* 不正なURL */
    return 'work/error/invalid_url';
  }
}
