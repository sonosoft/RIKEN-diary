<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/diary/controller.php
 */


/*
 * コントローラ
 */
class AdminDiaryController extends AdminController {
  /*
   * 一覧
   */
  protected function viewList(){
    /**/
    $this->useModel('Diary');
    $this->useValidator('DiarySearch');

    /* 検索条件検証 */
    $this->app->data['diary_search'] = $this->DiarySearchValidator->validate($this->app->data['diary_search']);

    /* リストオプション */
    $where = array();
    if($this->app->data['diary_search']['text_keys'] !== null){
      foreach($this->app->data['diary_search']['text_keys'] as $key){
	$where[] = sprintf('([code] = :%s OR INSTR([title], :%s) != 0 OR INSTR([overview], :%s) != 0)', $key, $key, $key);
      }
    }
    $where[] = '[status] = :enabled';
    /**/
    $options = array(
      'where'=>implode(' AND ', $where),
      'order'=>'[code] ASC',
      'pageSize'=>30,
      'indexSize'=>10,
      'page'=>$this->app->data['diary_search']['page'],
    );
    $parameters = $this->app->data['diary_search'];
    $parameters['enabled'] = STATUS_ENABLED;

    /* 検索 */
    list($this->app->data['diaries'], $this->app->data['paginator']) = $this->DiaryModel->page($options, $parameters);
    /**/
    $this->app->data['diary_search']['page'] = $this->app->data['paginator']->currentPage;
    $this->app->storeSession('diary_search', $this->app->data['diary_search']);

    /**/
    return 'admin/diary/list';
  }

  /*
   * フォーム
   */
  protected function viewForm(){
    /* 時間 */
    $this->app->data['hourChoices'] = array();
    foreach(range(0, 23) as $h){
      $this->app->data['hourChoices'][] = array('value'=>$h, 'label'=>sprintf('%02d', $h));
    }
    $this->app->data['minuteChoices'] = array();
    foreach(array(0, 15, 30, 45) as $m){
      $this->app->data['minuteChoices'][] = array('value'=>$m, 'label'=>sprintf('%02d', $m));
    }
    
    /**/
    return 'admin/diary/form';
  }
}
