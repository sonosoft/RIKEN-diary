<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/user/async_delete_action.php
 */


class AdminUserAsyncDeleteAction extends AdminUserController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('User', 'ProjectUser');

    /* リクエスト */
    if(($ids = json_decode($this->app->readRequest('ids'), true)) !== false){
      if(empty($ids) === false){
	/* 実行 */
	$this->db->begin();
	try{
	  /**/
	  foreach($ids as $id){
	    if(($user = $this->UserModel->findById($id)) !== null){
	      $user->status = STATUS_DISABLED;
	      $user->deleted_at = $this->app->data['_now_'];
	      $user->save();
	      /**/
	      $this->db->query('DELETE FROM project_user WHERE user_id = :id', array('id'=>$id));
	    }
	  }
	  
	  /**/
	  $this->db->commit();
	}catch(Exception $e){
	  $this->db->rollback();
	  $this->app->writeLog('admin/user/async_delete', $e->getMessage());
	}
      }
    }

    /**/
    echo json_encode(array());

    /**/
    $this->direct();
  }
}
