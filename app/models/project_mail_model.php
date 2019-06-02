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

  /* ===== ===== */

  public function getByProject($projectId){
    return $this->all(
      array(
	'joins'=>'mail',
	'where'=>'[project_id] = :project_id AND mail.status = :enabled',
	'order'=>'mail.code ASC',
      ),
      array('project_id'=>$projectId, 'enabled'=>STATUS_ENABLED)
    );
  }
}

class ProjectMailModel extends Model {
}
