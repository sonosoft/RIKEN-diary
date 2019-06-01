<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * work/print2_action.php
 */


class WorkPrint2Action extends WorkController {
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
    $calcium = $this->InquiryModel->calculateCa($this->user, $this->session, $pages);
    $foods = $this->InquiryModel->calculateF($this->user, $this->session, $pages);
    $this->InquiryModel->printCaF($this->user->code, $calcium, $foods);
    
    /**/
    $this->direct();
  }
}
