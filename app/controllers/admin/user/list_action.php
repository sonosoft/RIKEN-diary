<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/user/list_action.php
 */


class AdminUserListAction extends AdminUserController {
  /*
   * アクション
   */
  public function action(){
    /* セッションクリア */
    $this->app->removeSession('user_search');
    if(($projectId = intval($this->app->readRequest('project_id')))){
      $this->app->storeSession('user_search.project_id', $projectId);
    }
    
    /**/
    return $this->redirect('default:admin/user.search');
  }
}
