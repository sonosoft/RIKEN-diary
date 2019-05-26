<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/applicant/update_action.php
 */


class AdminApplicantUpdateAction extends AdminApplicantController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Applicant');

    /* トランザクション */
    $this->db->begin();

    /**/
    try{
      /* 保存 */
      $applicant = $this->ApplicantModel->one(
	array('where'=>'[id] = :id AND [deleted_at] IS NULL'),
	array('id'=>$this->app->readRequest('id'))
      );
      if($applicant !== null){
	$applicant->familyname = $this->app->readRequest('familyname');
	$applicant->firstname = $this->app->readRequest('firstname');
	$applicant->kana = $this->app->readRequest('kana');
	$applicant->email = $this->app->readRequest('email');
	$applicant->isMan = $this->app->readRequest('isMan');
	$applicant->birthdate = $this->app->readRequest('birthdate');
	$applicant->tel = $this->app->readRequest('tel');
	$applicant->postnumber = $this->app->readRequest('postnumber');
	$applicant->address = $this->app->readRequest('address');
	$applicant->isContactOk = $this->app->readRequest('isContactOk');
	$applicant->updated_at = $this->app->data['_now_'];
	$applicant->save();
      }

      /* コミット */
      $this->db->commit();
    }catch(Exception $e){
      /* ロールバック */
      $this->db->rollback();

      /* 例外 */
      $this->app->writeLog('admin/applicant/update', $e->getMessage());
    }

    /**/
    $this->redirect('default:admin/applicant.search');
  }
}
