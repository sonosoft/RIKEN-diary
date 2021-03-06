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
    return $this->one(array('where'=>'[token] = :token AND [status] = :enabled'), array('token'=>$token, 'enabled'=>STATUS_ENABLED));
  }
  
  public function collectChoices($default='', $all=false){
    $choices = array();
    if(empty($default) === false){
      $choices[] = array('value'=>'', 'label'=>$default);
    }
    if($all){
      $where = '[status] = :enabled';
    }else{
      $where = '[to_date] >= CURDATE() AND [status] = :enabled';
    }
    $records = $this->all(
      array('where'=>$where, 'order'=>'[from_date] ASC'),
      array('enabled'=>STATUS_ENABLED)
    );
    foreach($records as $record){
      $choices[] = array('value'=>$record->id, 'label'=>$record->tos);
    }
    return $choices;
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
  public function __isset($name){
    if(strcmp($name, 'tos') == 0){
      return true;
    }
    return false;
  }
  public function __get($name){
    if(strcmp($name, 'tos') == 0){
      return sprintf('%s [%s-%s]', $this->title, $this->from_date->format('%Y/%m/%d'), $this->to_date->format('%Y/%m/%d'));
    }
    return null;
  }
}
