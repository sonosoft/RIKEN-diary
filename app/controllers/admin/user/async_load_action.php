<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/user/async_load_action.php
 */


class AdminUserAsyncLoadAction extends AdminUserController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('User');

    /**/
    $result = array('id'=>0);
    
    /**/
    if(($user = $this->UserModel->findById($this->app->route['id'])) !== null){
      $result = $user->getAttributes();
    }
      
    /**/
    echo json_encode($result);
    $this->direct();
  }
}
