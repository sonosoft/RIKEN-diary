<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/download/form_action.php
 */


class AdminDownloadFormAction extends AdminDownloadController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Download');

    /* モデル */
    if($this->app->route['id'] !== null){
      $download = $this->DownloadModel->one(
	array('where'=>'[id] = :id'),
	array('id'=>$this->app->route['id'])
      );
      if($download === null){
	// $this->redirect('invalid_access_error');
      }
      $this->app->data['download'] = $download->getAttributes();
    }else{
      $this->app->data['download'] = array('id'=>null);
    }

    /**/
    return $this->viewForm();
  }

  /* ===== ===== */

  /*
   * コールバック [beforeSession()]
   */
  protected function beforeSession(){
    /**/
    parent::beforeSession();
  }

  /*
   * コールバック [beforeAction()]
   */
  protected function beforeAction(){
    /**/
    parent::beforeAction();
  }

  /*
   * コールバック [afterAction()]
   */
  protected function afterAction(){
    /**/
    parent::afterAction();
  }
}
