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
      $user = $this->UserModel->one(
	array('where'=>'[id] = :id AND [deleted_at] IS NULL'),
	array('id'=>$this->app->readRequest('id'))
      );
      if($user !== null){
	$user->familyname = $this->app->readRequest('familyname');
	$user->firstname = $this->app->readRequest('firstname');
	$user->kana = $this->app->readRequest('kana');
	$user->email = $this->app->readRequest('email');
	$user->isMan = $this->app->readRequest('isMan');
	$user->birthdate = $this->app->readRequest('birthdate');
	$user->tel = $this->app->readRequest('tel');
	$user->postnumber = $this->app->readRequest('postnumber');
	$user->address = $this->app->readRequest('address');
	$user->isContactOk = $this->app->readRequest('isContactOk');
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
