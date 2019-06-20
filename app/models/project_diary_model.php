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

  public function collectDiaries($visit){
    $diaries = array();
    foreach($this->getByProject($visit->project_id) as $entry){
      if($entry->diary->isActive($visit->started_at)){
	if($visit->diary_id !== null){
	  if($entry->diary->id == $visit->diary_id){
	    $diaries[] = $entry->diary;
	  }
	}else{
	  if(!$entry->diary->separated){
	    $diaries[] = $entry->diary;
	  }
	}
      }
    }
    return $diaries;
  }
}

class ProjectDiaryModel extends Model {
}
