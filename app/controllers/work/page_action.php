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
    $diaries = $this->ProjectDiaryModel->collectDiaries($this->visit);
    if(($pages = $this->PageModel->load($diaries)) === null){
      $this->app->writeLog('work/page #1', 'failed to read data file.');
      $this->redirect('default:work.error');
    }
    $indexes = $this->PageModel->collectIndexes($pages);
    if(empty($indexes)){
      $this->app->writeLog('work/page #2', 'failed to get page indexes.');
      $this->redirect('default:work.error');
    }

    /* ページ */
    if(isset($pages[$this->visit->page]) === false){
      $this->redirect('default:work.error');
    }
    if(($scale = $this->PageModel->getScale($pages[$this->visit->page])) !== false){
      $values = array();
    }else{
      list($this->app->data['rows'], $names, $values) = $this->PageModel->convert($pages[$this->visit->page]);
    }
    
    /**/
    $this->app->data['answer'] = $values;
    foreach($this->AnswerModel->collectByPage($this->user, $this->visit, $this->visit->page) as $answer){
      if($answer->listed){
	if(empty($answer->value) === false){
	  $this->app->data['answer'][$answer->name] = explode(',', $answer->value);
	}else{
	  $this->app->data['answer'][$answer->name] = array();
	}
      }else{
	$this->app->data['answer'][$answer->name] = $answer->value;
      }
    }

    /**/
    if($scale !== false){
      $this->app->data['scale'] = $scale;
      return 'work/scale';
    }
    return 'work/page';
  }
}
