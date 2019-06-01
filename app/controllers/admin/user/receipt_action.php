<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/user/receipt_action.php
 */


class AdminUserReceiptAction extends AdminUserController {
  /*
   * アクション
   */
  public function action(){
    /* 結果 */
    $this->app->data['uploaded_users'] = $this->app->restoreSession('uploaded_users', array());
    $this->app->removeSession('uploaded_users');
    
    /**/
    'admin/user/receipt';
  }
}
