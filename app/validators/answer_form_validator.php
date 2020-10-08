<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * answer_form_validator.php
 */


class AnswerFormValidator extends Validator {
  /*
   * 検証
   */
  protected function validateData(){
    /**/
    $this->notEmpty('date')->toDate('date');
    $this->validateIntegerIn('time', true, range(0, 2759));
    $this->validateIntegerIn('timing', true, array(TIMING_GETUP, TIMING_GOTOBED));
    /**/
    $this->validateIntegerIn('hour', true, range(-1, 27));
    $this->validateIntegerIn('minute', true, range(-1, 59));
    if($this->getValue('timing') == TIMING_GOTOBED){
      foreach(array('breakfast', 'lunch', 'dinner', 'work1', 'work2') as $key){
	$this->toBoolean('no_'.$key);
	$this->validateIntegerIn($key.'_hour', true, range(-1, 27));
	$this->validateIntegerIn($key.'_minute', true, range(-1, 59));
      }
      $this->notEmpty('other_index')->toInteger('other_index');
      if(($max = intval($this->getValue('other_index'))) > 0){
	foreach(range(1, $max) as $index){
	  $key = sprintf('other%02d', $index);
	  $this->ifEmpty($key.'_name', null);
	  $this->validateIntegerIn($key.'_hour1', true, range(-1, 27));
	  $this->validateIntegerIn($key.'_minute1', true, range(-1, 59));
	  $this->validateIntegerIn($key.'_hour2', true, range(-1, 27));
	  $this->validateIntegerIn($key.'_minute2', true, range(-1, 59));
	}
      }
    }
    /**/
    if($this->getValue('timing') == TIMING_GETUP){
      $keys = array('PHYSICAL', 'SLEEP', 'WAKING', 'MOTIVATION', 'SLEEPINESS', 'APPETITE', 'FATIGUE');
    }else if($this->getValue('timing') == TIMING_GOTOBED){
      $keys = array('APPETITE', 'PHYSICAL', 'MOTIVATION', 'SLEEPINESS1', 'SLEEPINESS2', 'FATIGUE', 'SATISFACTION', 'STRESS', 'DEPRESSION');
    }else{
      $keys = array();
    }
    $answers = array();
    foreach($keys as $key){
      $this->validateIntegerIn($key, true, range(1, 7));
      $answers[$key] = $this->getValue($key);
    }
    if($this->getValue('timing') == TIMING_GOTOBED){
      $this->validateIntegerIn('day', true, array(DAY_WORKDAY, DAY_HOLIDAY));
      $this->ifEmpty('medicine', '');
      $this->ifEmpty('memo', '');
      $answers['DAY'] = $this->getValue('day');
      $answers['MEDICINE'] = $this->getValue('medicine');
      $answers['MEMO'] = $this->getValue('memo');
    }
    $this->setValue('answers', json_encode($answers));
    /**/
    $this->ifEmpty('restoration_id', null)->toInteger('restoration_id');
    $this->validateIntegerIn('restoration_hour', false, range(0, 27));
    $this->validateIntegerIn('restoration_minute', false, range(0, 59));
  }
}
