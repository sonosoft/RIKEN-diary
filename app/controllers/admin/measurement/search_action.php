<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/measurement/search_action.php
 */


class AdminMeasurementSearchAction extends AdminMeasurementController {
  /*
   * アクション
   */
  public function action(){
    /* 検索条件 */
    if(($search = $this->app->readRequest('measurement_search', null)) === null){
      $search = $this->app->restoreSession('measurement_search', array());
      if(($page = $this->app->readRequest('p', null)) !== null){
        $search['page'] = $page;
      }
    }
    $this->app->data['measurement_search'] = $search;

    /**/
    return $this->viewList();
  }
}
