<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/error/invalid_session_action.php
 */


class AdminErrorInvalidSessionAction extends Controller {
  /*
   * アクション
   */
  public function action(){
    /* セッションクリア */
    $this->app->removeSession();

    /**/
    $this->app->data['error'] = 'セッションタイムアウト、または、不正なアクセスです。'.PHP_EOL.'ログインしてください。';
    
    /**/
    return 'admin/home/form';
  }
}
