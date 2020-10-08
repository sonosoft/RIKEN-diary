<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * restoration_search_validator.php
 */


class RestorationSearchValidator extends Validator {
  /*
   * 検証
   */
  protected function validateData(){
    /**/
    $this->validatePage('page');
    /**/
    $this->validateTextList('text');
    /**/
    $this->clearError();
  }
}
