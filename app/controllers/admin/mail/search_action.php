<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/mail/search_action.php
 */


class AdminMailSearchAction extends AdminMailController {
  /*
   * アクション
   */
  public function action(){
    /* 検索条件 */
    if(($search = $this->app->readRequest('mail_search', null)) === null){
      $search = $this->app->restoreSession('mail_search', array());
      if(($page = $this->app->readRequest('p', null)) !== null){
        $search['page'] = $page;
      }
    }
    $this->app->data['mail_search'] = $search;

    /**/
    return $this->viewList();
  }
}
