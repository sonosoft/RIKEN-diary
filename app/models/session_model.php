<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * session_model.php
 */


class SessionModelFactory extends ModelFactory {
  /*
   * プロパティ
   */
  protected $model = 'Session';
  protected $table = 'session';
  protected $relations = array(
    'administrator'=>array(
      'type'=>'belongsTo',
      'model'=>'Administrator',
      'conditions'=>array('administrator_id'=>'id')
    ),
  );

  /* ===== ===== */
  
  public function generateToken(){
    while(true){
      $token = sha1(mt_rand().time());
      if($this->one(array('where'=>'[token] = :token'), array('token'=>$token)) === null){
	return $token;
      }
    }
  }
}

class SessionModel extends Model {
}
