<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/project/save_action.php
 */


class AdminProjectSaveAction extends AdminProjectController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Project');
    $this->useValidator('ProjectForm');
    $this->useModule('Mail');

    /* トランザクション */
    $this->db->begin();

    /**/
    try{
      /* 検証 */
      $data = $this->ProjectFormValidator->validate($this->app->readRequest('project', array()));

      /* 保存 */
      if($data['id'] !== null){
	$project = $this->ProjectModel->one(
	  array('where'=>'[id] = :id'),
	  array('id'=>$data['id'])
	);
	if($project === null){
	  // $this->redirect('invalid_access_error');
	}
	$project->setAttributes($data);
      }else{
	$project = $this->ProjectModel->newModel($data);
      }
      $project->save();

      /* メール */
      if($data['id'] === null){
	$this->MailModule->reset();
       	$this->MailModule->setCharset('ISO-2022-JP');
	$this->MailModule->setHeaderEncoding('B');
	$this->MailModule->setSender('sender@system.app', 'Elnath');
	$this->MailModule->setRecipient('to', 'to@customer.app');
	$this->MailModule->setRecipient('bcc', 'bcc@syste.app');
	$this->MailModule->read('message_tempalte');
	$this->MailModule->assign('project', $data);
	$this->MailModule->send();
      }

      /* コミット */
      $this->db->commit();

      /* フラグ */
      if($data['id'] !== null){
	$this->app->storeSession('project_alert', 'Modified !!');
      }else{
	$this->app->storeSession('project_alert', 'Registered !!');
      }
    }catch(Exception $e){
      /* ロールバック */
      $this->db->rollback();

      /**/
      if($e instanceof Eln_Validation){
	/* エラー */
	$this->app->exportValidation($e, 'project');
	return $this->viewForm();
      }else if($e instanceof Eln_Redirection){
	/* リダイレクト */
	throw $e;
      }else{
	/* 例外 */
	// $this->app->writeLog('admin/project/save', $e->getMessage());
	// $this->redirect('unexpected_error');
      }
    }

    /**/
    $this->redirect('default:admin/project.search');
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
