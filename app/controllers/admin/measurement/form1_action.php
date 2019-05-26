<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/measurement/form1_action.php
 */


class AdminMeasurementForm1Action extends AdminMeasurementController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Measurement');
    
    /**/
    $this->app->data['measurement'] = array('hours'=>array(9, 10, 11, 12, 13), 'min'=>60);

    /**/
    $this->app->data['month'] = $this->app->data['_today_']->year * 100 + $this->app->data['_today_']->month;
    
    /**/
    return $this->viewForm1();
  }
}
