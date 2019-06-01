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
      if(($project = $this->ProjectModel->findById($this->app->route['id'])) === null){
	$this->redirect('default:admin/error.invalid_access');
      }
      $project->status = STATUS_DISABLED;
      $project->deleted_at = $this->app->data['_now_'];
      $project->save();

      /* コミット */
      $this->db->commit();
    }catch(Exception $e){
      /* ロールバック */
      $this->db->rollback();

      /**/
      if($e instanceof Eln_Redirection){
	/* リダイレクト */
	throw $e;
      }else{
	/* 例外 */
	$this->app->writeLog('admin/project/delete', $e->getMessage());
	$this->redirect('default:admin/error.unexpected');
      }
    }

    /**/
    $this->redirect('default:admin/project.search');
  }
}
