<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * user_form_validator.php
 */


class UserFormValidator extends Validator {
  /*
   * 検証
   */
  protected function validateData(){
    /**/
    $this->validateId('id');
    /**/
    $this->convert('name', 's')->trim('name')->notEmpty('name')->lengthLE('name', 20);
    $this->convert('code', 'as')->close('code')->notEmpty('code')->lengthLE('code', 20);
  }
}
