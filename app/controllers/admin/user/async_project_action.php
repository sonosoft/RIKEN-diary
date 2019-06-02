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
	if(($project = $this->ProjectModel->findById($this->app->readRequest('project', 0))) !== null){
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
	      $list = $this->ProjectUserModel->all(array('where'=>'[project_id] = :project_id'), array('project_id'=>$project->id));
	      $linkIds = array();
	      foreach($userIds as $userId){
		$found = false;
		foreach($list as $entry){
		  if($entry->user_id == $userId){
		    $linkIds[] = $entry->id;
		    $found = true;
		    break;
		  }
		}
		if($found === false){
		  $pu = $this->ProjectUserModel->newModel();
		  $pu->project_id = $project->id;
		  $pu->user_id = $userId;
		  $pu->save();
		  $linkIds[] = $pu->id;
		}
	      }
	      $this->db->query(
		'DELETE FROM project_user WHERE id NOT IN :ids AND project_id = :project_id',
		array('ids'=>$linkIds, 'project_id'=>$project->id)
	      );
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
    }

    /**/
    echo json_encode($result);

    /**/
    $this->direct();
  }
}
