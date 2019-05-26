<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/applicant/async_load_action.php
 */


class AdminApplicantAsyncLoadAction extends AdminApplicantController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Applicant', 'Measurement');

    /**/
    $result = array('id'=>0);
    
    /**/
    $applicant = $this->ApplicantModel->one(
      array('joins'=>'measurement', 'where'=>'[id] = :id AND [deleted_at] IS NULL'),
      array('id'=>$this->app->route['id'])
    );
    if($applicant !== null){
      $result = $applicant->getAttributes();
      $measurements = $this->MeasurementModel->availableDays($this->app->data['_today_']);
      $result['days'] = $this->MeasurementModel->arrangeDays($measurements);
    }
      
    /**/
    echo json_encode($result);
    $this->direct();
  }
}
