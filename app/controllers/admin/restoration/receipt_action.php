<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/restoration/receipt_action.php
 */


class AdminRestorationReceiptAction extends AdminController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Restoration');
    
    /**/
    $restoration = $this->RestorationModel->one(
      array('joins'=>array('project', 'user'), 'where'=>'[id] = :id AND [status] = :enabled'),
      array('id'=>$this->app->route['id'], 'enabled'=>STATUS_ENABLED)
    );
    if($restoration === null){
      $this->redirect('default:admin/error.invalid_access');
    }
    $this->app->data['restoration'] = $restoration;

    /**/
    return 'admin/restoration/receipt';
  }
}
