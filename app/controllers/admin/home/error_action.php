<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/home/error_action.php
 */


class AdminHomeErrorAction extends Controller {
  /*
   * アクション
   */
  public function action(){
    /* セッションクリア */
    $this->app->removeSession('admin');

    /* メッセージ */
    $this->app->data['error'] = '不正なアクセスです。';

    /**/
    return 'admin/home/form';
  }
}
