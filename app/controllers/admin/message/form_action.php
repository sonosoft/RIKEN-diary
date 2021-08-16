<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/message/form_action.php
 */


class AdminMessageFormAction extends AdminMessageController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('User', 'Mail', 'Message');

    /* リクエスト */
    if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') == 0){
      $ids = $this->app->readRequest('ids', array());
      if(empty($ids)){
	$this->redirect('default:admin/error.invalid_access');
      }
      $mail = null;
      $code = $this->app->readRequest('message');
      if(empty($code) === false){
	$mail = $this->MailModel->one(
	  array('where'=>'[code] = :code AND status = :enabled'),
	  array('code'=>$code, 'enabled'=>STATUS_ENABLED)
	);
      }
      if($mail !== null){
	$this->app->data['message'] = array(
	  'code'=>$mail->code,
	  'subject'=>$mail->title,
	  'body'=>$mail->body,
	);
	/*
	$this->app->data['message']['sent_on'] = $message->sent_at->format('%Y/%m/%d');
	$this->app->data['message']['sent_at_h'] = $message->sent_at->format('%H');
	$this->app->data['message']['sent_at_m'] = $message->sent_at->format('%M');
	if(($destinations = json_decode($message->destinations, true)) !== null){
	  foreach($ids as $id){
	    if(in_array($id, $destinations) === false){
	      $destinations[] = $id;
	    }
	  }
	  $this->app->data['message']['destinations'] = json_encode($destinations);
	}
	*/
      }else{
	$this->app->data['message'] = array();
      }
      $this->app->data['message']['destinations'] = json_encode($ids);
      $this->app->data['referer'] = 1;
    }else{
      $message = $this->MessageModel->one(
	array('where'=>'[id] = :id AND status = :enabled'),
	array('id'=>$this->app->route['id'], 'enabled'=>STATUS_ENABLED)
      );
      if($message === null){
	$this->redirect('default:admin/error.invalid_access');
      }
      $this->app->data['message'] = $message->getAttributes();
      $this->app->data['message']['sent_on'] = $message->sent_at->format('%Y/%m/%d');
      $this->app->data['message']['sent_at_h'] = $message->sent_at->format('%H');
      $this->app->data['message']['sent_at_m'] = $message->sent_at->format('%M');
      if(intval($this->app->readRequest('dup'))){
	$this->app->data['message']['id'] = null;
      }
      $this->app->data['referer'] = 2;
    }

    /**/
    return $this->viewForm();
  }
}
