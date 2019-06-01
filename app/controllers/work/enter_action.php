<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * work/enter_action.php
 */


class WorkEnterAction extends Controller {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('User', 'Session');
    $this->useValidator('EntranceForm');

    /**/
    $user = null;
    
    /* トランザクション */
    $this->db->begin();

    /**/
    try{
      /* 検証 */
      $data = $this->EntranceFormValidator->validate($this->app->readRequest('user', array()));

      /* ユーザ */
      if(($user = $this->UserModel->one(array('where'=>'[code] = :code'), array('code'=>$data['code']))) !== null){
	if(strcmp($user->birthday, $data['birthday']) != 0){
	  throw new Eln_Validation($data, array('birthday'=>'前回入力された[生年月日]と異なります。'));
	}
      }else{
	$user = $this->UserModel->newModel($data);
	$user->status = STATUS_ACTIVE;
	$user->registered_at = $this->app->data['_now_'];
	$user->save();
      }
      
      /* コミット */
      $this->db->commit();
    }catch(Exception $e){
      /* ロールバック */
      $this->db->rollback();

      /**/
      if($e instanceof Eln_Validation){
	$this->app->exportValidation($e, 'entrance');
	return 'work/form';
      }else if($e instanceof Eln_Redirection){
	/* リダイレクト */
	throw $e;
      }else{
	/* エラー */
	$this->app->writeLog('work/enter', $e->getMessage());
	$this->redirect('default:work.error');
      }
    }
    
    /* セッション */
    $session = $this->SessionModel->one(
      array(
	'where'=>'[user_id] = :user_id AND [accessed_at] >= DATE_SUB(NOW(), INTERVAL 15 DAY) AND [finished_at] IS NULL',
	'order'=>'[accessed_at] DESC',
      ),
      array('user_id'=>$user->id)
    );
    if($session !== null){
      /* 継続 */
      $this->app->data['user'] = $user;
      $this->app->storeSession('work_data.user_id', $user->id);
      $this->app->storeSession('work_data.session_id', $session->id);
      return 'work/select';
    }else{
      /* 開始 */
      $this->app->storeSession('work_data.user_id', $user->id);
      $this->redirect('default:work.start');
    }
  }
}
