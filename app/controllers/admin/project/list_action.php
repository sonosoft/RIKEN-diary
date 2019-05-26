<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/project/list_action.php
 */


class AdminProjectListAction extends AdminProjectController {
  /*
   * アクション
   */
  public function action(){
    /**/
    return 'admin/project/list';
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
