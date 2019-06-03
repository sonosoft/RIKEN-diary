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
    $this->useModel('Project', 'ProjectDiary', 'ProjectMail');

    /* モデル */
    if($this->app->route['id'] !== null){
      if(($project = $this->ProjectModel->findById($this->app->route['id'])) === null){
	$this->redirect('default:admin/error.invalid_access');
      }
      $this->app->data['project'] = $project->getAttributes();
      /**/
      $diaries = array();
      foreach($this->ProjectDiaryModel->getByProject($project->id) as $record){
	$diaries[] = array('id'=>$record->diary->id, 'text'=>$record->diary->tos);
      }
      $this->app->data['project']['diaries'] = json_encode($diaries);
      /**/
      $mails = array();
      foreach($this->ProjectMailModel->getByProject($project->id) as $record){
	$mails[] = array('id'=>$record->mail->id, 'text'=>$record->mail->tos);
      }
      $this->app->data['project']['mails'] = json_encode($mails);
    }else{
      $this->app->data['project'] = array('id'=>null, 'diaries'=>'[]', 'mails'=>'[]');
    }

    /**/
    return $this->viewForm();
  }
}
