<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * controller.php
 */


/*
 * コントローラ
 */
class Controller extends Eln_Controller {
  /*
   * プロパティ
   */
  protected $db = null;

  /* ===== ===== */

  /*
   * コールバック [beforeSession()]
   */
  protected function beforeSession(){
    /**/
    parent::beforeSession();

    /* セッション名 */
    $this->app->sessionName = 'ElnSSID';
  }

  /*
   * コールバック [beforeAction()]
   */
  protected function beforeAction(){
    /**/
    parent::beforeAction();

    /* データベース接続 */
    $this->db = Eln_Database::setCurrentDatabase('database');
    $this->db->open();

    /* 日付と時刻 */
    $this->app->data['_today_'] = Eln_Date::today();
    $this->app->data['_now_'] = Eln_Date::now();

    /* ルート */
    $this->app->data['_route_'] = $this->app->route;
  }

  /*
   * コールバック [afterAction()]
   */
  protected function afterAction(){
    /**/
    parent::afterAction();
  }
}
