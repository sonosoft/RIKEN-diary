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
	/* 当日 */
	if($this->app->data['_now_']->hour >= 4){
	  $today = $this->app->data['_today_'];
	}else{
	  $today = new Eln_Date($this->app->data['_today_']->getTime() - 86400);
	}
	
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
	if($project->from_date->compare($today) > 0 || $project->to_date->compare($today) < 0){
	  $this->db->rollback();
	  return 'work/error/out_of_date';
	}
	/**/
	$this->app->data['user'] = $user;
	$this->app->data['project'] = $project;
	
	/* 日誌 */
	$code = $this->app->route['code'];
	/**/
	$diaries = array();
	foreach($this->ProjectDiaryModel->getByProject($project->id) as $entry){
	  if($entry->diary->isActive($this->app->data['_now_'])){
	    if($code !== null){
	      if($entry->diary->separated && strcmp($entry->diary->code, $code) == 0){
		$diaries[] = $entry->diary;
	      }
	    }else{
	      if(!$entry->diary->separated){
		$diaries[] = $entry->diary;
	      }
	    }
	  }
	}
	if(empty($diaries)){
	  $this->db->rollback();
	  return 'work/error/out_of_time';
	}
	if(($pages = $this->PageModel->load($diaries)) === null){
	  $this->app->writeLog('work/index #1', 'failed to read data file.');
	  $this->redirect('default:work.error');
	}
	$indexes = $this->PageModel->collectIndexes($pages);
	if(empty($indexes)){
	  $this->app->writeLog('work/index #2', 'failed to get page indexes.');
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
	if($code !== null){
	  $visit->diary_id = $diaries[0]->id;
	}
	$visit->diaries = implode(',', $codes);
	$visit->visited_on = $today;
	if($this->app->data['_now_']->hour >= 4 && $this->app->data['_now_']->hour < 12){
	  $visit->timing = TIMING_GETUP;
	}else if($this->app->data['_now_']->hour >= 12 && $this->app->data['_now_']->hour < 17){
	  $visit->timing = TIMING_AM;
	}else if($this->app->data['_now_']->hour >= 17 && $this->app->data['_now_']->hour < 20){
	  $visit->timing = TIMING_PM;
	}else{
	  $visit->timing = TIMING_GOTOBED;
	}
	$visit->page = $indexes[0];
	$visit->status = STATUS_STARTED;
	$visit->started_at = $this->app->data['_now_'];
	$visit->save();
	/**/
	$prev = $this->VisitModel->one(
	  array(
	    'where'=>'[visited_on] = :visited_on AND [timing] = :timing AND [finished_at] IS NOT NULL',
	    'order'=>'[finished_at] DESC',
	  ),
	  array('visited_on'=>$visit->visited_on, 'timing'=>$visit->timing)
	);
	
	/* コミット */
	$this->db->commit();
	
	/* セッション */
	$this->app->data['prev'] = $prev;
	$this->app->data['visit'] = $visit;
	$this->app->storeSession('work_data.visit_id', $visit->id);
      }catch(Exception $e){
	var_dump($e);exit;
	/* ロールバック */
	$this->db->rollback();
	
	/* エラー */
	$this->app->writeLog('work/index', $e->getMessage());
	$this->redirect('default:work.error');
      }
      
      /**/
      return 'work/index';
    }
    
    /* 不正なURL */
    return 'work/error/invalid_url';
  }
}
