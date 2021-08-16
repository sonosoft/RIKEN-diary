<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/message/save_action.php
 */


class AdminMessageSaveAction extends AdminMessageController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Message');
    $this->useValidator('MessageForm');

    /* トランザクション */
    $this->db->begin();

    /**/
    try{
      /* 検証 */
      $data = $this->MessageFormValidator->validate($this->app->readRequest('message', array()));

      /* 保存 */
      if($data['id'] !== null){
	$message = $this->MessageModel->one(
	  array('where'=>'[id] = :id AND [status] = :enabled'),
	  array('id'=>$data['id'], 'enabled'=>STATUS_ENABLED)
	);
	if($message === null){
	  $this->redirect('default:admin/error.invalid_access');
	}
	$message->setAttributes($data);
      }else{
	$message = $this->MessageModel->newModel($data);
	$message->status = STATUS_ENABLED;
	$message->created_at = $this->app->data['_now_'];
      }
      $message->started_at = null;
      $message->finished_at = null;
      $message->updated_at = $this->app->data['_now_'];
      $message->save();

      /* コミット */
      $this->db->commit();
    }catch(Exception $e){
      /* ロールバック */
      $this->db->rollback();

      /**/
      if($e instanceof Eln_Validation){
	/* エラー */
	$this->app->exportValidation($e, 'message');
	$this->app->data['referer'] = $this->app->readRequest('referer');
	return $this->viewForm();
      }else if($e instanceof Eln_Redirection){
	/* リダイレクト */
	throw $e;
      }else{
	/* 例外 */
	$this->app->writeLog('admin/message/save', $e->getMessage());
	$this->redirect('default:admin/error.unexpected');
      }
    }

    /**/
    $this->redirect('default:admin/message.search');
  }
}
