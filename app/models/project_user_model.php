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
  );
}

class ProjectUserModel extends Model {
}
