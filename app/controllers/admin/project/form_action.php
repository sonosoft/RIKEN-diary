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
      if(($project = $this->ProjectModel->findById($this->app->route['id'])) === null){
	$this->redirect('default:admin/error.invalid_access');
      }
      $this->app->data['project'] = $project->getAttributes();
    }else{
      $this->app->data['project'] = array('id'=>null);
    }

    /**/
    return $this->viewForm();
  }
}
