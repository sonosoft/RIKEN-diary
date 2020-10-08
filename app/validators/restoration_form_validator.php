<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * restoration_form_validator.php
 */


class RestorationFormValidator extends Validator {
  /*
   * 検証
   */
  protected function validateData(){
    /**/
    $this->notEmpty('project_id')->toInteger('project_id');
    $this->notEmpty('user_id')->toInteger('user_id');
    $this->notEmpty('record')->toDate('date');
    $this->notEmpty('timing')->toInteger('timing');
  }
}
