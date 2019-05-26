<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/controller.php
 */


/*
 * コントローラ
 */
class AdminController extends Controller {
  /*
   * プロパティ
   */

  protected $session = null;

  /* ===== ===== */
  
  /*
   * コールバック [beforeAction()]
   */
  protected function beforeAction(){
    /**/
    parent::beforeAction();

    /**/
    $this->useModel('Session');

    /**/
    $this->db->begin();
    /**/
    try{
      /* セッション */
      $this->session = $this->SessionModel->one(
	array('joins'=>'administrator', 'where'=>'[token] = :token AND [updated_at] > DATE_SUB(NOW(), INTERVAL 30 MINUTE)'),
	array('token'=>$this->app->restoreSession('administrator.session', ''))
      );
      if($this->session === null){
	$this->redirect('default:admin/home.error');
      }

      /* 更新 */
      $this->session->updated_at = $this->app->data['_now_'];
      $this->session->save();

      /* コミット */
      $this->db->commit();
    }catch(Exception $e){
      /* エラー */
      $this->db->rollback();
      $this->app->writeLog('admin/*', $e->getMessage());
      $this->redirect('default:admin/home.error');
    }
  }
}
