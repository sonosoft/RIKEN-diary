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
    $this->useModel('User', 'Measurement');

    /* リクエスト */
    if(($ids = json_decode($this->app->readRequest('ids'), true)) !== false){
      if(empty($ids) === false){
	/* 実行 */
	$this->db->begin();
	try{
	  /**/
	  foreach($ids as $id){
	    $user = $this->UserModel->one(
	      array('joins'=>'measurement', 'where'=>'[id] = :id AND [deleted_at] IS NULL'),
	      array('id'=>$id)
	    );
	    if($user !== null){
	      $user->deleted_at = $this->app->data['_now_'];
	      $user->save();
	      if($user->measurement !== null){
		-- $user->measurement->currentCount;
		$user->measurement->save();
	      }
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
