<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/measurement/form2_action.php
 */


class AdminMeasurementForm2Action extends AdminMeasurementController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Measurement');

    /* モデル */
    $measurement = null;
    if($this->app->route['id'] !== null){
      $measurement = $this->MeasurementModel->findById($this->app->route['id']);
    }
    if($measurement === null){
      $this->redirect('default:admin/error.error');
    }
    $this->app->data['measurement'] = $measurement->getAttributes();

    /**/
    return $this->viewForm2();
  }
}
