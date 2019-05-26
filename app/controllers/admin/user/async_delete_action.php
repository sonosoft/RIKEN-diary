<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/applicant/async_delete_action.php
 */


class AdminApplicantAsyncDeleteAction extends AdminApplicantController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Applicant', 'Measurement');

    /* リクエスト */
    if(($ids = json_decode($this->app->readRequest('ids'), true)) !== false){
      if(empty($ids) === false){
	/* 実行 */
	$this->db->begin();
	try{
	  /**/
	  foreach($ids as $id){
	    $applicant = $this->ApplicantModel->one(
	      array('joins'=>'measurement', 'where'=>'[id] = :id AND [deleted_at] IS NULL'),
	      array('id'=>$id)
	    );
	    if($applicant !== null){
	      $applicant->deleted_at = $this->app->data['_now_'];
	      $applicant->save();
	      if($applicant->measurement !== null){
		-- $applicant->measurement->currentCount;
		$applicant->measurement->save();
	      }
	    }
	  }
	  
	  /**/
	  $this->db->commit();
	}catch(Exception $e){
	  $this->db->rollback();
	  $this->app->writeLog('admin/applicant/async_delete', $e->getMessage());
	}
      }
    }

    /**/
    echo json_encode(array());

    /**/
    $this->direct();
  }
}
