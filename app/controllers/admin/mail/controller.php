<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/mail/controller.php
 */


/*
 * コントローラ
 */
class AdminMailController extends AdminController {
  /*
   * 一覧
   */
  protected function viewList(){
    /**/
    $this->useModel('Mail');
    $this->useValidator('MailSearch');

    /* 検索条件検証 */
    $this->app->data['mail_search'] = $this->MailSearchValidator->validate($this->app->data['mail_search']);

    /* リストオプション */
    $where = array();
    if($this->app->data['mail_search']['text_keys'] !== null){
      foreach($this->app->data['mail_search']['text_keys'] as $key){
	$where[] = sprintf('([code] = :%s OR INSTR([title], :%s) != 0 OR INSTR([body], :%s) != 0)', $key, $key, $key);
      }
    }
    $where[] = '[status] = :enabled';
    /**/
    $options = array(
      'where'=>implode(' AND ', $where),
      'order'=>'[code] ASC',
      'pageSize'=>30,
      'indexSize'=>10,
      'page'=>$this->app->data['mail_search']['page'],
    );
    $parameters = $this->app->data['mail_search'];
    $parameters['enabled'] = STATUS_ENABLED;

    /* 検索 */
    list($this->app->data['mails'], $this->app->data['paginator']) = $this->MailModel->page($options, $parameters);
    /**/
    $this->app->data['mail_search']['page'] = $this->app->data['paginator']->currentPage;
    $this->app->storeSession('mail_search', $this->app->data['mail_search']);

    /**/
    return 'admin/mail/list';
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
    foreach(array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55) as $m){
      $this->app->data['minuteChoices'][] = array('value'=>$m, 'label'=>sprintf('%02d', $m));
    }
    
    /**/
    return 'admin/mail/form';
  }
}
