<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/project/search_action.php
 */


class AdminProjectSearchAction extends AdminProjectController {
  /*
   * アクション
   */
  public function action(){
    /* 検索条件 */
    if(($search = $this->app->readRequest('project_search', null)) === null){
      $search = $this->app->restoreSession('project_search', array());
      if(($page = $this->app->readRequest('p', null)) !== null){
        $search['page'] = $page;
      }
    }
    $this->app->data['project_search'] = $search;

    /**/
    return $this->viewList();
  }
}
