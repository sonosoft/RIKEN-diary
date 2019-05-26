<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/applicant/message_action.php
 */


class AdminApplicantMessageAction extends AdminApplicantController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Applicant', 'Message');

    /**/
    $applicants = null;
    
    /* リクエスト */
    $ids = $this->app->readRequest('ids', array());
    if(empty($ids) === false && is_array($ids)){
      $applicants = $this->ApplicantModel->all(
	array('joins'=>'measurement', 'where'=>'[id] IN :ids AND [deleted_at] IS NULL'),
	array('ids'=>$ids)
      );
    }
    if(empty($applicants)){
      $this->redirect('default:admin/applicant.search');
    }
    $this->app->data['applicants'] = array();
    foreach($applicants as $applicant){
      $this->app->data['applicants'][] = array(
	'id'=>$applicant->id,
	'code'=>$applicant->userID,
	'name'=>$applicant->familyname.' '.$applicant->firstname,
	'date'=>$applicant->measurement->measurementDate->format('%Y/%m/%d (%a) %H:%M'),
	'random'=>$applicant->randomString,
	'cancel'=>$applicant->randomString,
      );
    }

    /* メッセージ */
    $this->app->data['messageChoices'] = array();
    $this->app->data['messages'] = array();
    foreach($this->MessageModel->all(array('order'=>'[mailType] ASC, [title] ASC')) as $message){
      $this->app->data['messageChoices'][] = array('value'=>$message->id, 'label'=>$message->title);
      $this->app->data['messages'][] = $message;
    }

    /**/
    return 'admin/applicant/message';
  }
}
