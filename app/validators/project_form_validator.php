<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * project_form_validator.php
 */


class ProjectFormValidator extends Validator {
  /*
   * 検証
   */
  protected function validateData(){
    /**/
    $this->validateId('id');
    /**/
    $this->notEmpty('title')->lengthLE('title', 200);
    $this->notEmpty('from_date')->toDate('from_date');
    $this->notEmpty('to_date')->toDate('to_date');
    /**/
    $this->selection('diaries');
    if($this->isValid('diaries')){
      $json = str_replace('&quot;', '"', $this->getValue('diaries'));
      if(($data = json_decode($json, true)) === false || empty($data)){
	$this->setError('diaries', '指定されていません。');
      }else{
	$this->setValue('data_diaries', $data);
      }
    }
    /**/
    $this->selection('mails');
    if($this->isValid('mails')){
      $json = str_replace('&quot;', '"', $this->getValue('mails'));
      if(($data = json_decode($json, true)) === false || empty($data)){
	$this->setError('mails', '指定されていません。');
      }else{
	$this->setValue('data_mails', $data);
      }
    }
  }
}
