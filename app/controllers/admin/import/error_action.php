<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/import/error_action.php
 */


class AdminImportErrorAction extends AdminController {
  /*
   * アクション
   */
  public function action(){
    /* エラー */
    $this->app->data['upload_errors'] = $this->app->restoreSession('upload_errors', array());
    $this->app->removeSession('upload_errors');
    
    /**/
    return 'admin/import/error';
  }
}
