<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/user/error_action.php
 */


class AdminUserErrorAction extends AdminUserController {
  /*
   * アクション
   */
  public function action(){
    /* エラー */
    $this->app->data['upload_errors'] = $this->app->restoreSession('upload_errors', array());
    $this->app->removeSession('upload_errors');
    
    /**/
    return 'admin/user/error';
  }
}
