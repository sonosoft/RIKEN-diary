<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * restoration_model.php
 */


class RestorationModelFactory extends ModelFactory {
  /*
   * プロパティ
   */
  protected $model = 'Restoration';
  protected $table = 'restoration';
  protected $relations = array(
    'project'=>array(
      'type'=>'belongsTo',
      'model'=>'Project',
      'conditions'=>array('project_id'=>'id')
    ),
    'user'=>array(
      'type'=>'belongsTo',
      'model'=>'User',
      'conditions'=>array('user_id'=>'id')
    ),
  );

  /* ===== ===== */
  
  public function generateToken(){
    $base = array_merge(range(2, 9), range('a', 'k'), range('m', 'z'), range('A', 'H'), range('J', 'N'), range('P', 'Z'));
    while(true){
      $token = '';
      while(strlen($token) < 10){
	$token .= $base[array_rand($base)];
      }
      if($this->one(array('where'=>'[token] = :token'), array('token'=>$token)) === null){
	return $token;
      }
    }
  }
}

class RestorationModel extends Model {
}
