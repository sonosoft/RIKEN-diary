<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/error/unexpected_action.php
 */


class AdminErrorUnexpectedAction extends AdminController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->app->data['errorTitle'] = 'サーバエラー';
    $this->app->data['errorMessage'] = 'サーバで予期せぬエラーが発生しました。もう一度やり直してください。';
    
    /**/
    return 'admin/error/error';
  }
}
