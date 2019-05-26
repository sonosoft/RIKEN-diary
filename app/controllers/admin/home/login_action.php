<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/home/login_action.php
 */


class AdminHomeLoginAction extends Controller {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Administrator', 'Session');

    /* トランザクション */
    $this->db->begin();

    /**/
    try{
      /* 認証 */
      if(($administrator = $this->AdministratorModel->authenticate($this->app->readRequest('password'))) === null){
	throw new Eln_Validation(array(), array());
      }

      /* セッション */
      $session = $this->SessionModel->newModel();
      $session->administrator_id = $administrator->id;
      $session->token = $this->SessionModel->generateToken();
      $session->created_at = $this->app->data['_now_'];
      $session->updated_at = $this->app->data['_now_'];
      $session->save();
      /**/
      $this->app->storeSession('administrator.session', $session->token);

      /* コミット */
      $this->db->commit();
    }catch(Exception $e){
      /* ロールバック */
      $this->db->commit();

      /**/
      if($e instanceof Eln_Validation){
	/* ログイン失敗 */
	$this->app->data['error'] = 'ログインできません。'.PHP_EOL.'パスワードを確認してください。';
	return 'admin/home/form';
      }else{
	/* 例外 */
	$this->app->writeLog('admin/home/login', $e->getMessage());
	$this->redirect('default:admin/home.error');
      }
    }
    
    /* リダイレクト */
    $this->redirect('default:admin/project.list');
  }
}
