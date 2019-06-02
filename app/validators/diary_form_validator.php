<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * diary_form_validator.php
 */


class DiaryFormValidator extends Validator {
  /*
   * 検証
   */
  protected function validateData(){
    /**/
    $this->useModel('Diary');
    
    /**/
    $this->validateId('id');
    /**/
    $this->notEmpty('code')->pattern('code', '/^[0-9a-zA-Z]{3}$/', '英数字3文字で入力してください。');
    if($this->isValid('code')){
      if($this->DiaryModel->findByCode($this->getValue('code')) !== null){
	$this->setError('code', '同じIDがすでに登録されています。');
      }
    }
    /**/
    $this->notEmpty('title')->lengthLE('title', 200);
    $this->notEmpty('overview')->lengthLE('overview', 400);
    /**/
    $this->selection('from_time_h')->toInteger('from_time_h');
    $this->selection('from_time_m')->toInteger('from_time_m');
    if($this->isValid('from_time_h') && $this->isValid('from_time_m')){
      $this->setValue('from_time', intval($this->getValue('from_time_h')) * 100 + intval($this->getValue('from_time_m')));
    }
    $this->selection('to_time_h')->toInteger('to_time_h');
    $this->selection('to_time_m')->toInteger('to_time_m');
    if($this->isValid('to_time_h') && $this->isValid('to_time_m')){
      $this->setValue('to_time', intval($this->getValue('to_time_h')) * 100 + intval($this->getValue('to_time_m')));
    }
    /**/
    if(isset($_FILES['file']['error']) && $_FILES['file']['error'] == UPLOAD_ERR_OK){
      if(simplexml_load_file($_FILES['file']['tmp_name']) === false){
	$this->setError('file', 'XMLファイルではありません。');
      }
    }else{
      if(isset($_FILES['file']['error']) && $_FILES['file']['error'] == UPLOAD_ERR_NO_FILE && $this->isEmpty('id') === false){
	;
      }else{
	$this->setError('file', 'ファイルがアップロードされませんでした。');
      }
    }
  }
}
