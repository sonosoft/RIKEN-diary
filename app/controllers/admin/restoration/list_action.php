<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/restoration/list_action.php
 */


class AdminRestorationListAction extends AdminRestorationController {
  /*
   * アクション
   */
  public function action(){
    /* セッションクリア */
    $this->app->removeSession('restoration_search');
    
    /**/
    $this->redirect('default:admin/restoration.search');
  }
}
