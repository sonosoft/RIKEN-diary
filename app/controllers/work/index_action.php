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
    $this->useModel('User', 'Visit');
    
    /* 検索条件初期化 */
    $this->app->removeSession('work_data');
    $this->app->data['work_data'] = array();
    
    /* URL */
    var_dump($this->app->route['route']);
    if(strcmp($this->app->route['route'], 'entrance') == 0){
      /**/
      $this->db->begin();
      try{
	/* ユーザトークン */
	if(isset($this->app->route['token']) === false){
	  $this->db->rollback();
	  return 'work/error/invalid_url';
	}
	
	/* プロジェクト・ユーザ */
	if(($project = $this->ProjectModel->findByToken($this->app->route['token'])) === null){
	  $this->db->rollback();
	  return 'work/error/invalid_url';
	}
	$user = $project->users->user;
	/**/
	if($project->from_date->compare($this->app->data['_today_']) > 0 || $project->to_date->compare($this->app->data['_today_']) < 0){
	  $this->db->rollback();
	  return 'work/error/out_of_date';
	}
	
	/* 訪問 */
	$visit = $this->VisitModel->newModel();
	$visit->user_id = $user->id;
	$visit->project_id = $project->id;
	$visit->started_at = $this->app->data['_now_'];
	$visit->save();
	
	/* コミット */
	$this->db->commit();
	
	/* セッション */
	$this->app->data['user'] = $user;
	$this->app->storeSession('work_data.user_id', $user->id);
	$this->app->storeSession('work_data.visit_id', $visit->id);
      }catch(Exception $e){
	/* ロールバック */
	$this->db->rollback();
	
	/* エラー */
	$this->app->writeLog('work/index', $e->getMessage());
	$this->redirect('default:work/error.unexpected');
      }
    }else{
      /* 不正なURL */
      return 'work/error/invalid_url';
    }
    
    /**/
    return 'work/index';
  }
}
