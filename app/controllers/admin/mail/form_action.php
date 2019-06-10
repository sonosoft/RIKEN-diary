<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/mail/form_action.php
 */


class AdminMailFormAction extends AdminMailController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Mail');

    /* モデル */
    if($this->app->route['id'] !== null){
      if(($mail = $this->MailModel->findById($this->app->route['id'])) === null){
	$this->redirect('default:admin/error.invalid_access');
      }
      $this->app->data['mail'] = $mail->getAttributes();
      if(($schedule = json_decode($mail->schedule, true)) !== false){
	foreach($schedule as $key=>$value){
	  $this->app->data['mail'][$key] = $value;
	}
      }
      if(intval($this->app->readRequest('dup'))){
	$this->app->data['mail']['id'] = null;
      }
    }else{
      $this->app->data['mail'] = array('id'=>null, 'flag'=>MAIL_DURING);
    }
    /**/
    if(empty($this->app->data['mail']['times'])){
      $this->app->data['mail']['times'] = '[]';
    }else{
      $this->app->data['mail']['times'] = json_encode($this->app->data['mail']['times']);
    }
    
    /**/
    return $this->viewForm();
  }
}
