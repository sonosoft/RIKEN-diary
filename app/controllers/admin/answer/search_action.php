<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/answer/search_action.php
 */


class AdminAnswerSearchAction extends AdminAnswerController {
  /*
   * アクション
   */
  public function action(){
    /* 検索条件 */
    if(($search = $this->app->readRequest('answer_search', null)) === null){
      $search = $this->app->restoreSession('answer_search', array());
      if(($page = $this->app->readRequest('p', null)) !== null){
        $search['page'] = $page;
      }
    }
    $this->app->data['answer_search'] = $search;

    /**/
    return $this->viewList();
  }
}
