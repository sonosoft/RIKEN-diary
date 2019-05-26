<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/project/form_action.php
 */


class AdminProjectFormAction extends AdminProjectController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Project');

    /* モデル */
    if($this->app->route['id'] !== null){
      $project = $this->ProjectModel->one(
	array('where'=>'[id] = :id'),
	array('id'=>$this->app->route['id'])
      );
      if($project === null){
	// $this->redirect('invalid_access_error');
      }
      $this->app->data['project'] = $project->getAttributes();
    }else{
      $this->app->data['project'] = array('id'=>null);
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
