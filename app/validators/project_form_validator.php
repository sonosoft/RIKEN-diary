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
    $this->selection('mails');
  }
}
