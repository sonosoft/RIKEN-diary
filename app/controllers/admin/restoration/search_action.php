<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/restoration/search_action.php
 */


class AdminRestorationSearchAction extends AdminRestorationController {
  /*
   * アクション
   */
  public function action(){
    /* 検索条件 */
    if(($search = $this->app->readRequest('restoration_search', null)) === null){
      $search = $this->app->restoreSession('restoration_search', array());
      if(($page = $this->app->readRequest('p', null)) !== null){
        $search['page'] = $page;
      }
    }
    $this->app->data['restoration_search'] = $search;

    /**/
    return $this->viewList();
  }
}
