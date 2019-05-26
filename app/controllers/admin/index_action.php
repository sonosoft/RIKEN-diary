<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/index_action.php
 */


class AdminIndexAction extends Controller {
  /*
   * アクション
   */
  public function action(){
    /* セッションクリア */
    $this->app->removeSession('admin');

    /**/
    return 'admin/home/form';
  }
}
