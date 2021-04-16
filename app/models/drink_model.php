<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * drink_model.php
 */


class DrinkModelFactory extends ModelFactory {
  /*
   * プロパティ
   */
  protected $model = 'Drink';
  protected $table = 'drink';

  /* ===== ===== */

  public function getAvailableCodes($userId){
    $codes = [];
    $drinks = $this->all(
      array('where'=>'[user_id] = 0 AND [code] NOT IN (SELECT code FROM drink WHERE user_id = :user_id)'),
      array('user_id'=>$userId)
    );
    foreach($drinks as $drink){
      $codes[] = $drink->code;
    }
    return $codes;
  }
  
  public function getByUserId($userId){
    return $this->all(array('where'=>'[user_id] = :user_id'), array('user_id'=>$userId));
  }
}

class DrinkModel extends Model {
}
