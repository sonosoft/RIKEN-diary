<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/measurement/list_action.php
 */


class AdminMeasurementListAction extends AdminMeasurementController {
  /*
   * アクション
   */
  public function action(){
    /* セッションクリア */
    $this->app->removeSession('measurement_search');
      
    /**/
    return $this->redirect('default:admin/measurement.search');
  }
}
