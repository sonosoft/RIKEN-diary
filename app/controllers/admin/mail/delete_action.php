<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/mail/delete_action.php
 */


class AdminMailDeleteAction extends AdminMailController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Mail');

    /* トランザクション */
    $this->db->begin();

    /**/
    try{
      /* 削除 */
      if(($mail = $this->MailModel->findById($this->app->route['id'])) === null){
	$this->redirect('default:admin/error.invalid_access');
      }
      $mail->status = STATUS_DISABLED;
      $mail->deleted_at = $this->app->data['_now_'];
      $mail->save();

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
	$this->app->writeLog('admin/mail/delete', $e->getMail());
	$this->redirect('default:admin/error.unexpected');
      }
    }

    /**/
    $this->redirect('default:admin/mail.search');
  }
}
