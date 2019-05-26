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

    /* アラート */
    $this->app->data['project_alert'] = $this->app->restoreSession('project_alert');
    $this->app->removeSession('project_alert');

    /* 検索条件検証 */
    $this->app->data['project_search'] = $this->ProjectSearchValidator->validate($this->app->data['project_search']);

    /* リストオプション */
    $where = array();
    if($this->app->data['project_search']['text_keys'] !== null){
      foreach($this->app->data['project_search']['text_keys'] as $key){
	$where[] = sprintf('(INSTR([text1], :%s) != 0 OR INSTR([text2], :%s) != 0)', $key, $key);
      }
    }
    $where[] = '[status] = :active';
    /**/
    $options = array(
      'where'=>implode(' AND ', $where),
      'order'=>'[sequence_field] ASC',
      'pageSize'=>30,
      'indexSize'=>10,
      'page'=>$this->app->data['project_search']['page'],
    );
    $parameters = $this->app->data['project_search'];
    $parameters['active'] = STATUS_ACTIVE;

    /* 検索 */
    list($this->app->data['projects'], $this->app->data['paginator']) = $this->ProjectModel->page(
      $options,
      $parameters
    );
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
    return 'admin/project/form';
  }

  /* ===== ===== */

  /*
   * コールバック [beforeSession()]
   */
  protected function beforeSession(){
    /**/
    parent::beforeSession();
  }

  /*
   * コールバック [beforeAction()]
   */
  protected function beforeAction(){
    /**/
    parent::beforeAction();
  }

  /*
   * コールバック [afterAction()]
   */
  protected function afterAction(){
    /**/
    parent::afterAction();
  }
}
