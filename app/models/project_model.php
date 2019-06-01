<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * project_model.php
 */


class ProjectModelFactory extends ModelFactory {
  /*
   * プロパティ
   */
  protected $model = 'Project';
  protected $table = 'project';
  protected $relations = array(
    'mails'=>array(
      'type'=>'hasMany',
      'model'=>'ProjectMail',
      'conditions'=>array('id'=>'project_id')
    ),
    'diaries'=>array(
      'type'=>'hasMany',
      'model'=>'ProjectDiary',
      'conditions'=>array('id'=>'project_id')
    ),
    'users'=>array(
      'type'=>'hasMany',
      'model'=>'ProjectUser',
      'conditions'=>array('id'=>'project_id')
    ),
  );

  /* ===== ===== */

  public function findById($id){
    return $this->one(array('where'=>'[id] = :id AND [status] = :enabled'), array('id'=>$id, 'enabled'=>STATUS_ENABLED));
  }

  public function findByToken($token){
    $projectToken = substr($token, 0, 5);
    $userToken = substr($token, 5);
    return $this->one(
      array(
	'joins'=>array('users'=>'user'),
	'where'=>'[token] = :project AND users_user.token = :user AND [status] = :enabled AND users_user.token = :enabled',
      ),
      array('project'=>$projectToken, 'user'=>$userToken, 'enabled'=>STATUS_ENABLED)
    );
  }
  
  public function generateToken(){
    $base = array_merge(range(2, 9), range('a', 'k'), range('m', 'z'), range('A', 'H'), range('J', 'N'), range('P', 'Z'));
    while(true){
      $token = '';
      while(strlen($token) < 5){
	$token .= $base[array_rand($base)];
      }
      if($this->one(array('where'=>'[token] = :token'), array('token'=>$token)) === null){
	return $token;
      }
    }
  }
}

class ProjectModel extends Model {
}
