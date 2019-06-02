<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * work/page_action.php
 */


class WorkPageAction extends WorkController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Page', 'Visit', 'ProjectDiary', 'Answer');

    /* 日誌 */
    $diaries = array();
    $time = $this->visit->started_at->hour * 100 + $this->visit->started_at->minute;
    foreach($this->ProjectDiaryModel->getByProject($project->id) as $entry){
      if($entry->from_time <= $time && $entry->to_time >= $time){
	$diaries[] = $entry->diary;
      }
    }
    if(($pages = $this->PageModel->load($diaries)) === null){
      $this->app->writeLog('work/start #1', 'failed to read profile data file.');
      $this->redirect('default:work.error');
    }
    $indexes = $this->PageModel->collectIndexes($pages);
    if(empty($indexes)){
      $this->app->writeLog('work/start #1', 'failed to get page indexes.');
      $this->redirect('default:work.error');
    }

    /* ページ */
    if(isset($pages[$this->visit->page]) === false){
      $this->redirect('default:work.error');
    }
    list($this->app->data['rows'], $names, $values) = $this->PageModel->convert($pages[$this->visit->page]);
    
    /**/
    $this->app->data['answers'] = $values;
    foreach($this->AnswerModel->collectByPage($this->user, $this->visit, $this->visit->page) as $answer){
      if($answer->listed){
	if(empty($answer->value) === false){
	  $this->app->data['answers'][$answer->name] = explode(',', $answer->value);
	}else{
	  $this->app->data['answers'][$answer->name] = array();
	}
      }else{
	$this->app->data['answers'][$answer->name] = $answer->value;
      }
    }

    /**/
    return 'work/page';
  }
}
