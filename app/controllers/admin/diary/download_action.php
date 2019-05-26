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
    $this->useModel('Mail');

    /* XML */
    if(($mail = $this->MailModel->findById($data['id'])) === null){
      $this->redirect('default:admin/home.error');
    }
    /**/
    header('Content-type: application/xml');
    if(file_exists($mail->path)){
      readfile($mail->path);
    }

    /**/
    $this->direct();
  }
}
