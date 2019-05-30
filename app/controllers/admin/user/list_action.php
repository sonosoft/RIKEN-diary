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
      
    /**/
    return $this->redirect('default:admin/user.search');
  }
}
