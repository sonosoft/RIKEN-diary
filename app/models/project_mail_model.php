<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * project_mail_model.php
 */


class ProjectMailModelFactory extends ModelFactory {
  /*
   * プロパティ
   */
  protected $model = 'ProjectMail';
  protected $table = 'project_mail';
  protected $relations = array(
    'mail'=>array(
      'type'=>'belongsTo',
      'model'=>'Mail',
      'conditions'=>array('mail_id'=>'id')
    ),
  );
}

class ProjectMailModel extends Model {
}
