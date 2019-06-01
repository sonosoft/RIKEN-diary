<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * work/print1_action.php
 */


class WorkPrint1Action extends WorkController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Page', 'Inquiry');

    /**/
    if($this->user === null || $this->session === null){
      $this->redirect('default:work.error');
    }
    
    /* データ */
    if(($pages = $this->PageModel->load(null)) === null){
      $this->redirect('default:work.error');
    }

    /* PDF */
    list($date, $age, $sex) = $this->InquiryModel->getProfile($this->user, $this->session);
    $scores = $this->InquiryModel->calculateCHSI($this->user, $this->session, $pages);
    $this->InquiryModel->printCHSI($this->session->started_at, $this->user->code, $age, $sex, $scores);
    
    /**/
    $this->direct();
  }
}
