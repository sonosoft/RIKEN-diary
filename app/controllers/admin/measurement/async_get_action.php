<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/measurement/async_get_action.php
 */


class AdminMeasurementAsyncGetAction extends AdminMeasurementController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Measurement');
    
    /* 月度 */
    $month = $this->app->readRequest('month');
    $y1 = intval($month / 100);
    $m1 = intval($month % 100);
    $y2 = $y1;
    if(($m2 = $m1 + 1) > 12){
      $m2 = 1;
      ++ $y2;
    }

    /* 計測日 */
    $days1 = array();
    $measurements = $this->MeasurementModel->all(
      array('where'=>'YEAR([measurementDate]) = :y AND MONTH([measurementDate]) = :m'),
      array('y'=>$y1, 'm'=>$m1)
    );
    foreach($measurements as $measurement){
      $days1[] = $measurement->measurementDate->day;
    }
    /**/
    $days2 = array();
    $measurements = $this->MeasurementModel->all(
      array('where'=>'YEAR([measurementDate]) = :y AND MONTH([measurementDate]) = :m'),
      array('y'=>$y2, 'm'=>$m2)
    );
    foreach($measurements as $measurement){
      $days2[] = $measurement->measurementDate->day;
    }

    /**/
    $result = array(
      $this->getMonth($y1, $m1, $days1),
      $this->getMonth($y2, $m2, $days2),
    );
    /**/
    echo json_encode($result);

    /**/
    $this->direct();
  }

  /* ===== ===== */

  private function getMonth($y, $m, $days){
    $month = array('title'=>sprintf('%04d/%02d', $y, $m), 'calendar'=>array());
    $ym = strtotime(sprintf('%04d/%02d/01', $y, $m));
    $sz = date('t', $ym);
    if(($w = date('w', $ym)) == 0){
      $day = -5;
    }else{
      $day = 2 - $w;
    }
    for($week = 0; $week < 6; ++ $week){
      $row = array();
      for($d = 0; $d < 7; ++ $d){
	if($day <= 0 || $day > $sz){
	  $row[] = array('', 0);
	}else{
	  $row[] = array($day, (in_array($day, $days) ? 0 : 1));
	}
	++ $day;
      }
      $month['calendar'][] = $row;
    }
    return $month;
  }
}
