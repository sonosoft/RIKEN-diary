<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * work/index_action.php
 */


class WorkIndexAction extends Controller {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('User', 'Project', 'ProjectUser', 'ProjectDiary', 'Visit', 'Page');
    
    /* 検索条件初期化 */
    $this->app->removeSession('work_data');
    $this->app->data['work_data'] = array();
    
    /* URL */
    if(strcmp($this->app->route['route'], 'diary') == 0){
      /**/
      $this->db->begin();
      try{
	/* ユーザトークン */
	if(isset($this->app->route['token']) === false){
	  $this->db->rollback();
	  return 'work/error/invalid_url';
	}
	
	/* プロジェクト・ユーザ */
	if(($user = $this->UserModel->findByToken(substr($this->app->route['token'], 5))) === null){
	  $this->db->rollback();
	  return 'work/error/invalid_url';
	}
	if(($project = $this->ProjectModel->findByToken(substr($this->app->route['token'], 0, 5))) === null){
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
	if($project->from_date->compare($this->app->data['_today_']) > 0 || $project->to_date->compare($this->app->data['_today_']) < 0){
	  $this->db->rollback();
	  return 'work/error/out_of_date';
	}

	/* 日誌 */
	$diaries = array();
	$now = $this->app->data['_now_']->hour * 100 + $this->app->data['_now_']->minute;
	foreach($this->ProjectDiaryModel->getByProject($project->id) as $entry){
	  if($entry->diary->from_time <= $now && $entry->diary->to_time >= $now){
	    $diaries[] = $entry->diary;
	  }
	}
	if(($pages = $this->PageModel->load($diaries)) === null){
	  $this->app->writeLog('work/index #1', 'failed to read data file.');
	  $this->redirect('default:work.error');
	}
	var_dump($page);exit;
	$indexes = $this->PageModel->collectIndexes($pages);
	if(empty($indexes)){
	  $this->app->writeLog('work/index #2', 'failed to get page indexes.');
	  $this->redirect('default:work.error');
	}

	/* 訪問 */
	$visit = $this->VisitModel->newModel();
	$visit->user_id = $user->id;
	$visit->project_id = $project->id;
	$visit->page = $indexes[0];;
	$visit->status = STATUS_STARTED;
	$visit->started_at = $this->app->data['_now_'];
	$visit->save();
	
	/* コミット */
	$this->db->commit();
	
	/* セッション */
	$this->app->data['user'] = $user;
	$this->app->storeSession('work_data.visit_id', $visit->id);
      }catch(Exception $e){
	/* ロールバック */
	$this->db->rollback();
	
	/* エラー */
	$this->app->writeLog('work/index', $e->getMessage());
	$this->redirect('default:work.error');
      }
    }else{
      /* 不正なURL */
      return 'work/error/invalid_url';
    }
    
    /**/
    $this->redirect('default:work.page');
  }
}
