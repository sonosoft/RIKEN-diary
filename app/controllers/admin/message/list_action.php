<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/message/list_action.php
 */


class AdminMessageListAction extends AdminMessageController {
  /*
   * アクション
   */
  public function action(){
    /* セッションクリア */
    $this->app->removeSession('message_search');
    
    /**/
    $this->redirect('default:admin/message.search');
  }
}
