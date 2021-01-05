<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * work/history/controller.php
 */


/*
 * コントローラ
 */
class WorkHistoryController extends WorkController {
  /*
   * 一覧
   */
  protected function viewList(){
    /**/
    $this->useModel('Visit');

    /* 検索条件検証 */
    if(isset($this->app->data['work_history_search']['date'])){
      if(($date = strtotime($this->app->data['work_history_search']['date'])) === false){
	$date = $this->app->data['_today_'];
      }else{
	$date = new Eln_Date($date);
      }
    }else{
      $date = $this->app->data['_today_'];
    }
    $this->app->data['work_history_search']['date'] = $date;

    /* 日付 */
    $minDate = new Eln_Date($date->getTime() - 86400 * 4);
    $cnt = $minDate;
    $visits = array();
    while($cnt->compare($date) <= 0){
      $visits[] = array('date'=>$cnt->format('%Y/%m/%d'), 'getup'=>'-', 'am'=>'-', 'pm'=>'-', 'gotobed'=>'-');
      $cnt = new Eln_Date($cnt->getTime() + 86400);
    }
    
    /* 検索 */
    $where = array(
      '[project_id] = :project_id',
      '[user_id] = :user_id',
      '[visited_on] >= DATE_SUB(:date, INTERVAL 4 DAY)',
      '[visited_on] <= :date',
      '[finished_at] IS NOT NULL',
    );
    $records = $this->VisitModel->all(
      array('where'=>implode(' AND ', $where), 'order'=>'[visited_on] ASC, [timing] ASC'),
      array('project_id'=>$this->visit->project_id, 'user_id'=>$this->user->id, 'date'=>$date)
    );
    $timings = array(TIMING_GETUP=>'getup', TIMING_AM=>'am', TIMING_PM=>'pm', TIMING_GOTOBED=>'gotobed');
    foreach($records as $record){
      $index = intval(($record->visited_on->getTime() - $minDate->getTime()) / 86400);
      $visits[$index][$timings[$record->timing]] = '済';
    }
    $this->app->data['visits'] = $visits;
    /**/
    $this->app->storeSession('work_history_search', $this->app->data['work_history_search']);

    /**/
    return 'work/history/list';
  }
}
