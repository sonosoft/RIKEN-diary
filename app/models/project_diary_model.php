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
    // current time.
    $time = $visit->started_at->hour * 100 + $visit->started_at->minute;

    // collect.
    $diaries = array();
    foreach($this->getByProject($visit->project_id) as $entry){
      if($entry->diary->from_time <= $time && $entry->diary->to_time >= $time){
	if($visit->diary_id !== null){
	  if($entry->diary->id == $visit->diary_id){
	    $diaries[] = $entry->diary;
	  }
	}else{
	  $diaries[] = $entry->diary;
	}
      }
    }

    //
    return $diaries;
  }
}

class ProjectDiaryModel extends Model {
}
