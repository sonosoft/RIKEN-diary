<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * project_diary_model.php
 */


class ProjectDiaryModelFactory extends ModelFactory {
  /*
   * プロパティ
   */
  protected $model = 'ProjectDiary';
  protected $table = 'project_diary';
  protected $relations = array(
    'diary'=>array(
      'type'=>'belongsTo',
      'model'=>'Diary',
      'conditions'=>array('diary_id'=>'id')
    ),
  );

  /* ===== ===== */

  public function getByProject($projectId){
    return $this->all(
      array(
	'joins'=>'diary',
	'where'=>'[project_id] = :project_id AND diary.status = :enabled',
	'order'=>'diary.code ASC',
      ),
      array('project_id'=>$projectId, 'enabled'=>STATUS_ENABLED)
    );
  }
}

class ProjectDiaryModel extends Model {
}
