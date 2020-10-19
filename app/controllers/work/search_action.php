<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * work/history/search_action.php
 */


class WorkHistorySearchAction extends WorkHistoryController {
  /*
   * アクション
   */
  public function action(){
    /* 検索条件 */
    if(($search = $this->app->readRequest('work_history_search', null)) === null){
      $search = $this->app->restoreSession('work_history_search', array());
    }
    $this->app->data['work_history_search'] = $search;

    /**/
    return $this->viewList();
  }
}
