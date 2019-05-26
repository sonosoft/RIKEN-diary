<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/import/receipt_action.php
 */


class AdminImportReceiptAction extends AdminController {
  /*
   * アクション
   */
  public function action(){
    /* ユーザ */
    $this->app->data['uploaded_users'] = $this->app->restoreSession('uploaded_users', array());
    $this->app->removeSession('uploaded_users');
    
    /**/
    return 'admin/import/receipt';
  }
}
