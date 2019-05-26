<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/diary/list_action.php
 */


class AdminDiaryListAction extends AdminDiaryController {
  /*
   * アクション
   */
  public function action(){
    /* セッションクリア */
    $this->app->removeSession('diary_search');
    
    /**/
    $this->redirect('default:admin/diary.search');
  }
}
