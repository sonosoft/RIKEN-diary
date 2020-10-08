<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/answer/list_action.php
 */


class AdminAnswerListAction extends AdminAnswerController {
  /*
   * アクション
   */
  public function action(){
    /* セッションクリア */
    $this->app->removeSession('answer_search');
    
    /**/
    $this->redirect('default:admin/answer.search');
  }
}
