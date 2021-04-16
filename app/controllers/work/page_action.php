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
    $this->useModel('Page', 'Visit', 'ProjectDiary', 'Answer', 'Drink');

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
      if(isset($scale['time'])){
	$values[$scale['name'].'_time_h'] = $this->app->data['_now_']->hour;
	$values[$scale['name'].'_time_m'] = intval($this->app->data['_now_']->minute / 5) * 5;
      }
    }else{
      list($this->app->data['rows'], $names, $values) = $this->PageModel->convert($pages[$this->visit->page]);
    }

    /* 飲料 */
    $drink = false;
    foreach($this->app->data['rows'] as $row){
      foreach($row['items'] as $item){
	if($item['name'] == 'Q-B-1_1' || $item['name'] = 'Q-C-1_1'){
	  $drink = true;
	  break;
	}
      }
    }
    if($drink){
      $this->app->data['availableCodes'] = $this->DrinkModel->getAvailableCodes($this->user->id);
    }else{
      $this->app->data['availableCodes'] = [];
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
