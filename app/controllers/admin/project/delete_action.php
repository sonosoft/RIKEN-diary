<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/project/delete_action.php
 */


class AdminProjectDeleteAction extends AdminProjectController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Project');

    /* トランザクション */
    $this->db->begin();

    /**/
    try{
      /* 削除 */
      $project = $this->ProjectModel->one(
        array('where'=>'[id] = :id'),
	array('id'=>$this->app->route['id'])
      );
      if($project === null){
	// $this->redirect('invalid_access_error');
      }
      $project->status = STATUS_REMOVED;
      $project->save();
      // $project->delete();

      /* コミット */
      $this->db->commit();

      /* フラグ */
      $this->app->storeSession('project_alert', 'Deleted !!');
    }catch(Exception $e){
      /* ロールバック */
      $this->db->rollback();

      /**/
      if($e instanceof Eln_Redirection){
	/* リダイレクト */
	throw $e;
      }else{
	/* 例外 */
	// $this->app->writeLog('admin/project/delete', $e->getMessage());
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
  protected function after_action(){
    /**/
    parent::afterAction();
  }
}
