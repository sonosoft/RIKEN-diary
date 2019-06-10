<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/project/async_collect_users_action.php
 */


class AdminProjectAsyncCollectUsersAction extends AdminProjectController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('ProjectUser');

    /**/
    $result = array();
    
    /* ユーザ */
    foreach($this->ProjectUserModel->getByProject($this->app->route['id']) as $user){
      $result[] = $user->user->getAttributes();
    }
    
    /**/
    echo json_encode($result);

    /**/
    $this->direct();
  }
}
