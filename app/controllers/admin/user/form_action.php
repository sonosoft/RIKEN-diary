<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/user/form_action.php
 */


class AdminUserFormAction extends AdminUserController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('User');

    /* モデル */
    if($this->app->route['id'] !== null){
      $user = $this->UserModel->one(
	array('where'=>'[id] = :id'),
	array('id'=>$this->app->route['id'])
      );
      if($user === null){
	// $this->redirect('invalid_access_error');
      }
      $this->app->data['user'] = $user->getAttributes();
    }else{
      $this->app->data['user'] = array('id'=>null);
    }

    /**/
    return $this->viewForm();
  }

  /* ===== ===== */

  /*
   * コールバック [beforeSession()]
   */
  protected function beforeSession(){
    /**/
    parent::beforeSession();
  }

  /*
   * コールバック [beforeAction()]
   */
  protected function beforeAction(){
    /**/
    parent::beforeAction();
  }

  /*
   * コールバック [afterAction()]
   */
  protected function afterAction(){
    /**/
    parent::afterAction();
  }
}
