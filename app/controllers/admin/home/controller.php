<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/home/controller.php
 */


/*
 * コントローラ
 */
class AdminHomeController extends AdminController {
  /*
   * 一覧
   */
  protected function viewList(){
    /**/
    $this->useModel('Home');
    $this->useValidator('HomeSearch');

    /* アラート */
    $this->app->data['home_alert'] = $this->app->restoreSession('home_alert');
    $this->app->removeSession('home_alert');

    /* 検索条件検証 */
    $this->app->data['home_search'] = $this->HomeSearchValidator->validate($this->app->data['home_search']);

    /* リストオプション */
    $where = array();
    if($this->app->data['home_search']['text_keys'] !== null){
      foreach($this->app->data['home_search']['text_keys'] as $key){
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
      'page'=>$this->app->data['home_search']['page'],
    );
    $parameters = $this->app->data['home_search'];
    $parameters['active'] = STATUS_ACTIVE;

    /* 検索 */
    list($this->app->data['homes'], $this->app->data['paginator']) = $this->HomeModel->page(
      $options,
      $parameters
    );
    /**/
    $this->app->data['home_search']['page'] = $this->app->data['paginator']->currentPage;
    $this->app->storeSession('home_search', $this->app->data['home_search']);

    /**/
    return 'admin/home/list';
  }

  /*
   * フォーム
   */
  protected function viewForm(){
    /**/
    return 'admin/home/form';
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
