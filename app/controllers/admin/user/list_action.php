<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/applicant/list_action.php
 */


class AdminApplicantListAction extends AdminApplicantController {
  /*
   * アクション
   */
  public function action(){
    /* セッションクリア */
    $this->app->removeSession('applicant_search');
      
    /**/
    return $this->redirect('default:admin/applicant.search');
  }
}
