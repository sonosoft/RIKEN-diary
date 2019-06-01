<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * work/controller.php
 */


/*
 * コントローラ
 */
class WorkController extends Controller {
  /*
   * プロパティ
   */
  protected $workData = null;
  protected $user = null;
  protected $session = null;

  /* ===== ===== */
  
  /*
   * コールバック [beforeAction()]
   */
  protected function beforeAction(){
    /**/
    parent::beforeAction();

    /**/
    $this->useModel('User', 'Session');

    /* セッションデータ */
    $this->workData = $this->app->restoreSession('work_data', array());
    if(isset($this->workData['user_id'])){
      $this->user = $this->UserModel->one(array('where'=>'[id] = :user_id'), $this->workData);
    }
    if($this->user === null){
      $this->redirect('default:work.error');
    }
    if(isset($this->workData['session_id'])){
      $this->db->begin();
      try{
	if(($this->session = $this->SessionModel->one(array('joins'=>'system', 'where'=>'[id] = :session_id'), $this->workData)) !== null){
	  $this->session->accessed_at = $this->app->data['_now_'];
	  $this->session->save();
	}
	$this->db->commit();
      }catch(Exception $e){
	$this->db->rollback();
	$this->app->writeLog('work/*', $e->getMessage());
      }
    }

    /**/
    $this->app->data['user'] = $this->user;
  }
}
