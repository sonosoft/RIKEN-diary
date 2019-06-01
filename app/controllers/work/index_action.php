<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * work/index_action.php
 */


class WorkIndexAction extends Controller {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('User', 'Session');
    
    /* 検索条件初期化 */
    $this->app->removeSession('work_data');
    $this->app->data['work_data'] = array();
    
    /* URL */
    if(strcmp($this->app->route['route'], 'entrance') == 0){
      /**/
      $this->db->begin();
      try{
	/* ユーザトークン */
	if(isset($this->app->route['token']) === false){
	  throw new Exception('No user token.');
	}
	$token = $this->app->route['token'];
	
	/* 検証 */
	$rsrc = $this->db->query(
	  'SELECT userID FROM applicants WHERE randomString = :token AND deleted_at IS NULL',
	  array('token'=>$token)
	);
	if($rsrc === false){
	  throw new Exception(sprintf('Invalid user token: "%s".', $token));
	}
	if(($row = $rsrc->fetch_row()) === null){
	  throw new Exception(sprintf('Invalid user token: "%s".', $token));
	}
	$code = $row[0];
	
	/* ユーザ */
	if(($user = $this->UserModel->one(array('where'=>'[token] = :token'), array('token'=>$token))) === null){
	  $user = $this->UserModel->newModel();
	  $user->token = $token;
	  $user->status = 1;
	  $user->registered_at = $this->app->data['_now_'];
	}
	$user->code = trim($code);
	$user->save();
	
	/* セッション */
	$session = $this->SessionModel->one(
	  array('where'=>'[user_id] = :user_id AND [finished_at] IS NOT NULL', 'order'=>'[finished_at] DESC'),
	  array('user_id'=>$user->id)
	);
	if($session !== null){
	  $this->app->data['is_done'] = $session->finished_at->format('%Y/%m/%d');
	}
	/**/
	$session = $this->SessionModel->one(
	  array(
	    'where'=>'[user_id] = :user_id AND [accessed_at] >= DATE_SUB(NOW(), INTERVAL 15 DAY) AND [finished_at] IS NULL',
	    'order'=>'[accessed_at] DESC',
	  ),
	  array('user_id'=>$user->id)
	);
	
	/* コミット */
	$this->db->commit();
	
	/**/
	if($session !== null){
	  /* 継続 */
	  $this->app->data['user'] = $user;
	  $this->app->storeSession('work_data.user_id', $user->id);
	  $this->app->storeSession('work_data.session_id', $session->id);
	  return 'work/select';
	}else{
	  /* 表紙 */
	  $this->app->data['user'] = $user;
	  $this->app->storeSession('work_data.user_id', $user->id);
	  return 'work/index';
	}
      }catch(Exception $e){
	/* ロールバック */
	$this->db->rollback();
	
	/* エラー */
	$this->app->writeLog('work/index', $e->getMessage());
	$this->redirect('default:work.error');
      }
    }else{
      /* ログインフォーム */
      return 'work/form';
    }
    
    /**/
    $this->redirect('default:work.error');
  }
}
