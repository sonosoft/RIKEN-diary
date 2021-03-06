<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/user/search_action.php
 */


class AdminUserSearchAction extends AdminUserController {
  /*
   * アクション
   */
  public function action(){
    /* 検索条件 */
    if(($search = $this->app->readRequest('user_search', null)) === null){
      $search = $this->app->restoreSession('user_search', array());
      if(($page = $this->app->readRequest('p', null)) !== null){
        $search['page'] = $page;
      }else if(($order = $this->app->readRequest('o', null)) !== null){
        $search['order'] = $order;
      }
    }
    $this->app->data['user_search'] = $search;

    /**/
    return $this->viewList();
  }
}
