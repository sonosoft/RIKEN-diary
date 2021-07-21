<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/user/update_action.php
 */


class AdminUserUpdateAction extends AdminUserController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('User');

    /* トランザクション */
    $this->db->begin();

    /**/
    try{
      /* 保存 */
      if(($user = $this->UserModel->findById($this->app->readRequest('id'))) !== null){
	$user->family_name = $this->app->readRequest('family_name');
	$user->first_name = $this->app->readRequest('first_name');
	$user->kana = $this->app->readRequest('kana');
	$user->email = $this->app->readRequest('email');
	$user->email_alt = $this->app->readRequest('email_alt');
	$user->sex = $this->app->readRequest('sex');
	$user->birthday = new Eln_Date(strtotime($this->app->readRequest('birthday')));
	$user->updated_at = $this->app->data['_now_'];
	$user->save();
      }

      /* コミット */
      $this->db->commit();
    }catch(Exception $e){
      /* ロールバック */
      $this->db->rollback();

      /* 例外 */
      $this->app->writeLog('admin/user/update', $e->getMessage());
    }

    /**/
    $this->redirect('default:admin/user.search');
  }
}
