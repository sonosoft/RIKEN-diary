<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * message_form_validator.php
 */


class MessageFormValidator extends Validator {
  /*
   * 検証
   */
  protected function validateData(){
    /**/
    $this->validateId('id');
    /**/
    $this->notEmpty('code');
    /**/
    $this->notEmpty('subject')->lengthLE('subject', 200);
    $this->notEmpty('body')->lengthLE('body', 4000);
    /**/
    $this->notEmpty('sent_on', '送信日が指定されていません。')->toDate('sent_on', '送信日は日付の形式で指定してください。');
    $this->ifEmpty('sent_at_h', 0)->toInteger('sent_at_h');
    $this->ifEmpty('sent_at_m', 0)->toInteger('sent_at_m');
    if($this->isValid('sent_on')){
      $date = $this->getValue('sent_on')->format('%Y/%m/%d');
      $this->setValue(
	'sent_at',
	new Eln_Date(strtotime($date.' '.$this->getValue('sent_at_h').':'.$this->getValue('sent_at_m')))
      );
    }
    /**/
    $destinations = json_decode($this->getValue('destinations'), true);
    if(empty($destinations)){
      $this->setValue('destinations', '[]');
      $this->setError('destinations', '送信先が指定されていません。');
    }
  }
}
