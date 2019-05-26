<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/measurement/controller.php
 */


/*
 * コントローラ
 */
class AdminMeasurementController extends AdminController {
  /*
   * 一覧
   */
  protected function viewList(){
    /**/
    $this->useModel('Measurement');
    $this->useValidator('MeasurementSearch');

    /* 検索条件検証 */
    $this->app->data['measurement_search'] = $this->MeasurementSearchValidator->validate($this->app->data['measurement_search']);

    /* リストオプション */
    $where = array();
    if($this->app->data['measurement_search']['month'] != 0){
      $where[] = 'YEAR([measurementDate]) = :y';
      $where[] = 'MONTH([measurementDate]) = :m';
    }
    /**/
    $options = array(
      'order'=>'DATE([measurementDate]) DESC, TIME([measurementDate]) ASC',
      'pageSize'=>30,
      'indexSize'=>10,
      'page'=>$this->app->data['measurement_search']['page'],
    );
    if(empty($where) === false){
      $options['where'] = implode(' AND ', $where);
    }
    $parameters = $this->app->data['measurement_search'];

    /* 検索 */
    list($this->app->data['measurements'], $this->app->data['paginator']) = $this->MeasurementModel->page($options, $parameters);
    /**/
    $this->app->data['measurement_search']['page'] = $this->app->data['paginator']->currentPage;
    $this->app->storeSession('measurement_search', $this->app->data['measurement_search']);

    /* 月度 */
    $this->app->data['monthChoices'] = array();
    $this->app->data['monthChoices'][] = array('value'=>0, 'label'=>'全て');
    $max = $this->MeasurementModel->one(array('order'=>'[measurementDate] DESC'));
    $min = $this->MeasurementModel->one(array('order'=>'[measurementDate] ASC'));
    if($max !== null && $min !== null){
      $y = $max->measurementDate->year;
      $m = $max->measurementDate->month;
      $minYM = $min->measurementDate->year * 100 + $min->measurementDate->month;
      while(($y * 100 + $m) >= $minYM){
	$this->app->data['monthChoices'][] = array('value'=>($y * 100 + $m), 'label'=>sprintf('%04d/%02d', $y, $m));
	-- $m;
	if($m == 0){
	  $m = 12;
	  -- $y;
	}
      }
    }
    
    /**/
    return 'admin/measurement/list';
  }

  /*
   * フォーム
   */
  protected function viewForm1(){
    /* 月度 */
    $y = $this->app->data['_today_']->year;
    if(($m = $this->app->data['_today_']->month + 3) > 12){
      $m -= 12;
      ++ $y;
    }
    $this->app->data['monthChoices'] = array();
    for($i = 0; $i < 6; ++ $i){
      $this->app->data['monthChoices'][] = array('value'=>($y * 100 + $m), 'label'=>sprintf('%04d/%02d', $y, $m));
      -- $m;
      if($m == 0){
	$m = 12;
	-- $y;
      }
    }
    
    /**/
    return 'admin/measurement/form1';
  }

  /*
   * フォーム
   */
  protected function viewForm2(){
    /**/
    return 'admin/measurement/form2';
  }
}
