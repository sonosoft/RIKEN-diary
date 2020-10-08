<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/restoration/controller.php
 */


/*
 * コントローラ
 */
class AdminRestorationController extends AdminController {
  /*
   * 一覧
   */
  protected function viewList(){
    /**/
    $this->useModel('Restoration');
    $this->useValidator('RestorationSearch');

    /* 検索条件検証 */
    $this->app->data['restoration_search'] = $this->RestorationSearchValidator->validate($this->app->data['restoration_search']);

    /* リストオプション */
    $where = array();
    if($this->app->data['restoration_search']['text_keys'] !== null){
      foreach($this->app->data['restoration_search']['text_keys'] as $key){
	$where[] = sprintf('(INSTR(project.title, :%s) != 0 OR INSTR(user.code, :%s) != 0)', $key, $key);
      }
    }
    $where[] = '[status] = :enabled';
    /**/
    $options = array(
      'joins'=>array('project', 'user'),
      'where'=>implode(' AND ', $where),
      'order'=>'user.code ASC, project.title ASC',
      'pageSize'=>30,
      'indexSize'=>10,
      'page'=>$this->app->data['restoration_search']['page'],
    );
    $parameters = $this->app->data['restoration_search'];
    $parameters['enabled'] = STATUS_ENABLED;

    /* 検索 */
    list($this->app->data['restorations'], $this->app->data['paginator']) = $this->RestorationModel->page($options, $parameters);
    /**/
    $this->app->data['restoration_search']['page'] = $this->app->data['paginator']->currentPage;
    $this->app->storeSession('restoration_search', $this->app->data['restoration_search']);

    /**/
    return 'admin/restoration/list';
  }
}
