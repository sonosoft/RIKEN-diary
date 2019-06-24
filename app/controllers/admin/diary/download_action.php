<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/diary/download_action.php
 */


class AdminDiaryDownloadAction extends AdminDiaryController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Diary');

    /* XML */
    if(($diary = $this->DiaryModel->findById($this->app->route['id'])) === null){
      $this->redirect('default:admin/home.error');
    }
    /**/
    header('Content-type: application/xml');
    if(file_exists($diary->path)){
      readfile($diary->path);
    }

    /**/
    $this->direct();
  }
}
