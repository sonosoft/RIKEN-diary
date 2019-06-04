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
  protected $visit = null;

  /* ===== ===== */
  
  /*
   * コールバック [beforeAction()]
   */
  protected function beforeAction(){
    /**/
    parent::beforeAction();

    /**/
    $this->useModel('User', 'Visit');

    /* セッションデータ */
    $this->workData = $this->app->restoreSession('work_data', array());

    /* 訪問 */
    if(($this->visit = $this->VisitModel->findById($this->workData['visit_id'])) === null){
      $this->app->writeLog('work/* #1', 'No visit found.');
      $this->redirect('default:work.error');
    }
    if(($this->user = $this->UserModel->findById($this->visit->user_id)) === null){
      $this->app->writeLog('work/* #2', 'No user found.');
      $this->redirect('default:work.error');
    }
    $this->db->begin();
    try{
      $this->visit->accessed_at = $this->app->data['_now_'];
      $this->visit->save();
      $this->db->commit();
    }catch(Exception $e){
      $this->db->rollback();
      $this->app->writeLog('work/*', $e->getMessage());
    }
    
    /**/
    $this->app->data['user'] = $this->user;
  }
}
