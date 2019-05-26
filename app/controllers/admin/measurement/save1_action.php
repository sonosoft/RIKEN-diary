.<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/measurement/save1_action.php
 */


class AdminMeasurementSave1Action extends AdminMeasurementController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Measurement');

    /* トランザクション */
    $this->db->begin();

    /**/
    try{
      /* フォーム */
      $hours = $this->app->readRequest('measurement.hours', array());
      $limitDate = new Eln_Date(strtotime($this->app->readRequest('measurement.limitDate')));
      $min = intval($this->app->readRequest('measurement.min'));
      $isDisplay = (($this->app->readRequest('measurement.isDisplay', 0) != 0) ? 1 : 0);

      /**/
      $index = $this->MeasurementModel->getNextIndex();
      
      /* 保存 */
      if(($days = json_decode($this->app->readRequest('measurement.days'), true)) !== false){
	asort($days);
	asort($hours);
	foreach($days as $day){
	  foreach($hours as $hour){
	    $measurement = $this->MeasurementModel->newModel();
	    $measurement->manageIndex = $index;
	    $measurement->isDisplay = $isDisplay;
	    $measurement->measurementDate = new Eln_Date(strtotime(sprintf('%s %02d:00:00', $day, $hour)));
	    $measurement->currentCount = 0;
	    $measurement->min = $min;
	    $measurement->reservationLimitDay = intval(($measurement->measurementDate->getTime() - $limitDate->getTime()) / 86400);
	    $measurement->limitDate = $limitDate;
	    $measurement->created_at = $this->app->data['_now_'];
	    $measurement->updated_at = $this->app->data['_now_'];
	    $measurement->save();
	    ++ $index;
	  }
	}
      }

      /* コミット */
      $this->db->commit();
    }catch(Exception $e){
      /* ロールバック */
      $this->db->rollback();

      /**/
      if($e instanceof Eln_Redirection){
	/* リダイレクト */
	throw $e;
      }else{
	/* 例外 */
	$this->app->writeLog('admin/measurement/save1', $e->getMessage());
	$this->redirect('default:admin/error.error');
      }
    }

    /**/
    $this->redirect('default:admin/measurement.search');
  }
}
