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
    /* セッションクリア */
    $this->app->removeSession('project_search');
    
    /**/
    $this->redirect('default:admin/project.search');
  }
}
