<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/error/invalid_access_action.php
 */


class AdminErrorInvalidAccessAction extends AdminController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->app->data['errorTitle'] = '不正なアクセス';
    $this->app->data['errorMessage'] = '不正なアクセスが検出されました。他の操作をお試しください。';
    
    /**/
    return 'admin/error/error';
  }
}
