<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * user_search_validator.php
 */


class UserSearchValidator extends Validator {
  /*
   * 検証
   */
  protected function validateData(){
    /**/
    $this->validatePage('page');
    /**/
    $this->validateTextList('text');
    $this->ifEmpty('project_id', null)->toInteger('project_id');
    $this->ifEmpty('order', 'i-d');
    /**/
    $this->toBoolean('all');
    /**/
    $this->toBoolean('download');
    /**/
    $this->clearError();
  }
}
