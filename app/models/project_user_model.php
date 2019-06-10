<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * project_user_model.php
 */


class ProjectUserModelFactory extends ModelFactory {
  /*
   * プロパティ
   */
  protected $model = 'ProjectUser';
  protected $table = 'project_user';
  protected $relations = array(
    'user'=>array(
      'type'=>'belongsTo',
      'model'=>'User',
      'conditions'=>array('user_id'=>'id')
    ),
    'project'=>array(
      'type'=>'belongsTo',
      'model'=>'Project',
      'conditions'=>array('project_id'=>'id')
    ),
  );

  /* ===== ===== */

  public function getByProject($projectId){
    return $this->all(
      array(
	'joins'=>'user',
	'where'=>'[project_id] = :project_id AND user.status = :enabled',
	'order'=>'user.code ASC',
      ),
      array('project_id'=>$projectId, 'enabled'=>STATUS_ENABLED)
    );
  }

  public function getByUser($userId){
    return $this->all(
      array(
	'joins'=>'project',
	'where'=>'[user_id] = :user_id AND project.status = :enabled',
	'order'=>'project.from_date ASC',
      ),
      array('user_id'=>$userId, 'enabled'=>STATUS_ENABLED)
    );
  }
}

class ProjectUserModel extends Model {
}
