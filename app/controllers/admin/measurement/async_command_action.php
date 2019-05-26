<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/measurement/async_command_action.php
 */


class AdminMeasurementAsyncCommandAction extends AdminMeasurementController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Measurement');

    /* リクエスト */
    if(($ids = json_decode($this->app->readRequest('ids'), true)) !== false){
      if(empty($ids) === false){
	/* 実行 */
	$this->db->begin();
	try{
	  /**/
	  $command = $this->app->readRequest('command');
	  if($command == 0 || $command == 1){
	    $this->db->query(
	      'UPDATE measurement_dates SET isDisplay = :value WHERE id IN :ids',
	      array('ids'=>$ids, 'value'=>$command)
	    );
	  }else if($command == 9){
	    $this->db->query(
	      'DELETE FROM measurement_dates WHERE id IN :ids AND isDisplay = 0 AND currentCount = 0',
	      array('ids'=>$ids)
	    );
	  }
	  
	  /**/
	  $this->db->commit();
	}catch(Exception $e){
	  $this->db->rollback();
	  $this->app->writeLog('admin/measurement/async_command', $e->getMessage());
	}
      }
    }

    /**/
    echo json_encode(array());

    /**/
    $this->direct();
  }
}
