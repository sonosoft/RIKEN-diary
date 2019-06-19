<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/user/async_project_action.php
 */


class AdminUserAsyncProjectAction extends AdminUserController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('User', 'Project', 'ProjectUser');

    /**/
    $result = array('status'=>0);
    
    /* リクエスト */
    if(($ids = json_decode($this->app->readRequest('ids'), true)) !== false){
      if(empty($ids) === false){
	/* 実行 */
	$this->db->begin();
	try{
	  /**/
	  $userIds = array();
	  foreach($ids as $id){
	    if(($user = $this->UserModel->findById($id)) !== null){
	      $userIds[] = $user->id;
	    }
	  }
	  if(empty($userIds) === false){
	    /* 現在のリンクを解除 */
	    $this->db->query('DELETE FROM project_user WHERE user_id IN :ids', array('ids'=>$userIds));

	    /* プロジェクト */
	    if(($project = $this->ProjectModel->findById($this->app->readRequest('project', 0))) !== null){
	      /* リンク作成（プロジェクトに追加） */
	      foreach($userIds as $userId){
		$pu = $this->ProjectUserModel->newModel();
		$pu->project_id = $project->id;
		$pu->user_id = $userId;
		$pu->save();
	      }
	    }
	  }
	  
	  /**/
	  $this->db->commit();
	  
	  /**/
	  $result['status'] = 1;
	}catch(Exception $e){
	  $this->db->rollback();
	  $this->app->writeLog('admin/user/async_project', $e->getMessage());
	}
      }
    }

    /**/
    echo json_encode($result);

    /**/
    $this->direct();
  }
}
