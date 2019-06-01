<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * work/finish_action.php
 */


class WorkFinishAction extends WorkController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Page', 'Inquiry');

    /* データ */
    if($this->session === null){
      $this->app->writeLog('work/finish', 'invalid session.');
      $this->redirect('default:work.error');
    }
    if(($pages = $this->PageModel->load($this->session->system)) === null){
      $this->app->writeLog('work/finish', 'failed to load pages.');
      $this->redirect('default:work.error');
    }
    
    /**/
    $this->app->data['results'] = array();
    
    /* CHSI */
    if($this->session->system->target03){
      $this->app->data['results']['CHSI'] = $this->InquiryModel->calculateCHSI($this->user, $this->session, $pages);
    }

    /* CALCIUM */
    if($this->session->system->target11){
      $this->app->data['results']['Ca'] = $this->InquiryModel->calculateCa($this->user, $this->session, $pages);
    }
    
    /* MEAL */
    if($this->session->system->target12){
      $this->app->data['results']['F'] = $this->InquiryModel->calculateF($this->user, $this->session, $pages);
    }

    /**/
    if(empty($this->app->data['results'])){
      $this->app->data['results'] = null;
    }
    
    /**/
    return 'work/finish';
  }
}
