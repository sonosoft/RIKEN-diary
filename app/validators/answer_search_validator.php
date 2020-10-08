<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * answer_search_validator.php
 */


class AnswerSearchValidator extends Validator {
  /*
   * 検証
   */
  protected function validateData(){
    /**/
    $this->validatePage('page');
    /**/
    $this->ifEmpty('project_id', null)->toInteger('project_id');
    $this->ifEmpty('from', null)->toDate('from');
    $this->ifEmpty('to', null)->toDate('to');
    /**/
    $this->toBoolean('download');
    /**/
    $this->clearError();
  }
}
