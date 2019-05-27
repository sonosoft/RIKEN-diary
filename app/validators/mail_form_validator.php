<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * mail_form_validator.php
 */


class MailFormValidator extends Validator {
  /*
   * 検証
   */
  protected function validateData(){
    /**/
    $this->useModel('Mail');
    
    /**/
    $this->validateId('id');
    /**/
    $this->notEmpty('code')->pattern('code', '/^[0-9a-zA-Z]{3}$/', '英数字3文字で入力してください。');
    if($this->isValid('code')){
      if($this->MailModel->findByCode($this->getValue('code')) !== null){
	$this->setError('code', '同じIDがすでに登録されています。');
      }
    }
    /**/
    $this->notEmpty('title')->lengthLE('title', 200);
    $this->notEmpty('body')->lengthLE('body', 4000);
    /**/
    $schedule = array();
    $this->validateIntegerIn('flag', true, array(MAIL_BEFORE, MAIL_AFTER, MAIL_DURING, MAIL_DATE));
    if($this->isValid('flag')){
      $schedule['flag'] = $this->getValue('flag');
      switch($schedule['flag']){
      case MAIL_BEFORE:
	$this->notEmpty('before', '日数が指定されていません。')->toInteger('before', '日数は整数で指定してください。');
	if($this->isValid('before')){
	  $schedule['before'] = $this->getValue('before');
	}
	break;
      case MAIL_AFTER:
	$this->notEmpty('after', '日数が指定されていません。')->toInteger('after', '日数は整数で指定してください。');
	if($this->isValid('after')){
	  $schedule['after'] = $this->getValue('after');
	}
	break;
      case MAIL_DATE:
	$this->notEmpty('date', '送信日が指定されていません。')->toDate('date', '送信日は日付の形式で指定してください。');
	if($this->isValid('date')){
	  $schedule['date'] = $this->getValue('date')->format('%Y/%m/%d');
	}
	break;
      }
      if(empty($this->getValue('times')) === false && is_array($this->getValue('times'))){
	$schedule['times'] = $this->getValue('times');
      }else{
	$this->setError('times', '指定されていません。');
      }
    }
    $this->setValue('schedule', json_encode($schedule));
  }
}
