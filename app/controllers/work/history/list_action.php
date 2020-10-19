<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * work/history/list_action.php
 */


class WorkHistoryListAction extends WorkHistoryController {
  /*
   * アクション
   */
  public function action(){
    /* セッションクリア */
    $this->app->removeSession('work_history_search');

    /**/
    $this->redirect('default:work/history.search');
  }
}
