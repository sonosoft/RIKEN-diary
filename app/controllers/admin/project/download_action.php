<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/project/download_action.php
 */


class AdminProjectDownloadAction extends AdminProjectController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Project', 'Visit', 'Answer', 'Page');

    /* モデル */
    if(($project = $this->ProjectModel->findById($this->app->route['id'])) !== null){
      $this->redirect('default:admin/error.invalid_access');
    }

    /**/
    return $this->viewForm();
  }
}
