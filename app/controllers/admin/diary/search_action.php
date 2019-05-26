<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/diary/search_action.php
 */


class AdminDiarySearchAction extends AdminDiaryController {
  /*
   * アクション
   */
  public function action(){
    /* 検索条件 */
    if(($search = $this->app->readRequest('diary_search', null)) === null){
      $search = $this->app->restoreSession('diary_search', array());
      if(($page = $this->app->readRequest('p', null)) !== null){
        $search['page'] = $page;
      }
    }
    $this->app->data['diary_search'] = $search;

    /**/
    return $this->viewList();
  }
}
