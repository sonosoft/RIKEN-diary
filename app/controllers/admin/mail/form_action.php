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
    }else{
      $this->app->data['mail'] = array('id'=>null, 'flag'=>MAIL_DURING);
    }
    /**/
    if(empty($this->app->datap['mail']['times'])){
      $this->app->data['mail']['times'] = '[]';
    }else{
      $this->app->data['mail']['times'] = json_encode($this->app->datap['mail']['times']);
    }
    
    /**/
    return $this->viewForm();
  }
}
