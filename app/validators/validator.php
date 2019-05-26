<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * validator.php
 */


class Validator extends Eln_Validator {
  /*
   * 整数
   */
  protected function validateInteger($name, $required, $default){
    /**/
    $this->convert($name, 'as')->close($name);
    if($required){
      $this->notEmpty($name);
    }else{
      $this->ifEmpty($name, $default);
    }
    $this->toInteger($name);
  }
  protected function validateIntegerIn($name, $required, $values){
    /**/
    if($required){
      $this->selection($name);
    }else{
      $this->ifEmpty($name, null);
    }
    $this->toInteger($name);
    if($this->isValid($name)){
      if(is_array($values) === false || in_array($this->getValue($name), $values) === false){
	$this->setValue($name, null);
	if($required){
	  $this->selection($name);
	}
      }
    }
  }
  /**/
  protected function validateId($name='id'){
    /**/
    $this->ifEmpty($name, null)->toInteger($name);
    if($this->isValid($name) === false){
      $this->setValue($name, 0);
      $this->clearError($name);
    }
  }
  protected function validatePage($name='page'){
    /**/
    $this->ifEmpty($name, 1)->toInteger($name);
    if($this->isValid($name) === false){
      $this->setValue($name, 1);
      $this->clearError($name);
    }
  }

  /*
   * テキストリスト
   */
  protected function validateTextList($name){
    /**/
    $textKeys = array();
    $textIndex = 0;
    $this->convert($name, 's')->trim($name)->ifEmpty($name, '');
    if($this->isEmpty($name) === false){
      foreach(preg_split('/\s+/', $this->getValue($name)) as $text){
	$key = sprintf('%s_%d', $name, $textIndex);
	$textKeys[] = $key;
	$this->setValue($key, $text);
	++ $textIndex;
      }
    }
    if(empty($textKeys)){
      $this->setValue($name.'_keys', null);
    }else{
      $this->setValue($name.'_keys', $textKeys);
    }
  }
}
