<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * message_search_validator.php
 */


class MessageSearchValidator extends Validator {
  /*
   * 検証
   */
  protected function validateData(){
    /**/
    $this->validatePage('page');
    $this->ifEmpty('order', 'i-d');
    /**/
    $this->validateTextList('text');
    /**/
    $this->ifEmpty('from', null)->toDate('from');
    $this->ifEmpty('to', null)->toDate('to');
    /**/
    $this->toBoolean('download');
    /**/
    $this->clearError();
  }
}
