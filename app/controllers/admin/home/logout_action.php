<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/home/logout_action.php
 */


class AdminHomeLogoutAction extends Controller {
  /*
   * アクション
   */
  public function action(){
    /* セッションクリア */
    $this->app->removeSession('admin');

    /* メッセージ */
    $this->app->data['message'] = 'ログアウトしました。';

    /**/
    return 'admin/home/form';
  }
}
