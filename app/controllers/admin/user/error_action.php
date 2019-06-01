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
    $this->app->data['uploaded_errors'] = $this->app->restoreSession('uploaded_errors', array());
    $this->app->removeSession('uploaded_errors');
    
    /**/
    return 'admin/user/error';
  }
}
