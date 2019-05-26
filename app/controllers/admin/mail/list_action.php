<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/mail/list_action.php
 */


class AdminMailListAction extends AdminMailController {
  /*
   * アクション
   */
  public function action(){
    /* セッションクリア */
    $this->app->removeSession('mail_search');
    
    /**/
    $this->redirect('default:admin/mail.search');
  }
}
