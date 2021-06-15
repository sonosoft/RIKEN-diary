<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * work/rewind_action.php
 */


class WorkRewindAction extends WorkController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Project');
    
    /* 当日 */
    if($this->app->data['_now_']->hour >= 4){
      $today = $this->app->data['_today_'];
    }else{
      $today = new Eln_Date($this->app->data['_today_']->getTime() - 86400);
    }

    /* プロジェクト */
    if(($project = $this->ProjectModel->findById($this->visit->project_id)) === null){
      return 'work/error/invalid_url';
    }
    $this->app->data['project'] = $project;

    /* セッション */
    $this->app->data['user'] = $this->user;
    $this->app->data['visit'] = $this->visit;
      
    /**/
    return 'work/index';
  }
}
