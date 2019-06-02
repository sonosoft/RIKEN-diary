<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/project/controller.php
 */


/*
 * コントローラ
 */
class AdminProjectController extends AdminController {
  /*
   * 一覧
   */
  protected function viewList(){
    /**/
    $this->useModel('Project');
    $this->useValidator('ProjectSearch');

    /* 検索条件検証 */
    $this->app->data['project_search'] = $this->ProjectSearchValidator->validate($this->app->data['project_search']);

    /* リストオプション */
    $where = array();
    if($this->app->data['project_search']['text_keys'] !== null){
      foreach($this->app->data['project_search']['text_keys'] as $key){
	$where[] = sprintf('(INSTR([text1], :%s) != 0 OR INSTR([text2], :%s) != 0)', $key, $key);
      }
    }
    $where[] = '[status] = :enabled';
    /**/
    $options = array(
      'where'=>implode(' AND ', $where),
      'order'=>'[id] ASC',
      'pageSize'=>30,
      'indexSize'=>10,
      'page'=>$this->app->data['project_search']['page'],
    );
    $parameters = $this->app->data['project_search'];
    $parameters['enabled'] = STATUS_ENABLED;

    /* 検索 */
    list($this->app->data['projects'], $this->app->data['paginator']) = $this->ProjectModel->page($options, $parameters);
    /**/
    $this->app->data['project_search']['page'] = $this->app->data['paginator']->currentPage;
    $this->app->storeSession('project_search', $this->app->data['project_search']);

    /**/
    return 'admin/project/list';
  }

  /*
   * フォーム
   */
  protected function viewForm(){
    /**/
    $this->useModel('Diary', 'Mail');
    
    /* 選択肢 */
    $this->app->data['diaryChoices'] = $this->DiaryModel->collectChoices('選択してください');
    $this->app->data['mailChoices'] = $this->MailModel->collectChoices('選択してください');
    
    /**/
    return 'admin/project/form';
  }
}
