<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/measurement/save2_action.php
 */


class AdminMeasurementSave2Action extends AdminMeasurementController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Measurement');
    $this->useValidator('MeasurementForm');

    /* トランザクション */
    $this->db->begin();

    /**/
    try{
      /* 検証 */
      $data = $this->MeasurementFormValidator->validate($this->app->readRequest('measurement', array()));
      unset($data['measurementDate']);

      /* 保存 */
      $measurement = null;
      if($data['id'] !== null){
	$measurement = $this->MeasurementModel->findById($data['id']);
      }
      if($measurement === null){
	$this->redirect('default:admin/error.error');
      }
      $measurement->setAttributes($data);
      $measurement->updated_at = $this->app->data['_now_'];
      $measurement->save();

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
	$this->app->writeLog('admin/measurement/save2', $e->getMessage());
	$this->redirect('default:admin/error.error');
      }
    }

    /**/
    $this->redirect('default:admin/measurement.search');
  }
}
