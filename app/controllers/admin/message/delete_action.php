<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/message/delete_action.php
 */


class AdminMessageDeleteAction extends AdminMessageController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Message');

    /* トランザクション */
    $this->db->begin();

    /**/
    try{
      /* 削除 */
      $message = $this->MessageModel->one(
	array('where'=>'[id] = :id AND status = :enabled'),
	array('id'=>$this->app->route['id'], 'enabled'=>STATUS_ENABLED)
      );
      if($message === null){
	$this->redirect('default:admin/error.invalid_access');
      }
      $message->status = STATUS_DISABLED;
      $message->deleted_at = $this->app->data['_now_'];
      $message->save();

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
	$this->app->writeLog('admin/message/delete', $e->getMessage());
	$this->redirect('default:admin/error.unexpected');
      }
    }

    /**/
    $this->redirect('default:admin/message.search');
  }
}
