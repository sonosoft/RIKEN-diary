<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/mail/save_action.php
 */


class AdminMailSaveAction extends AdminMailController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Mail');
    $this->useValidator('MailForm');

    /* トランザクション */
    $this->db->begin();

    /**/
    try{
      /* 検証 */
      $data = $this->MailFormValidator->validate($this->app->readRequest('mail', array()));

      /* 保存 */
      if($data['id'] !== null){
	if(($mail = $this->MailModel->findById($data['id'])) === null){
	  $this->redirect('default:admin/home.error');
	}
	$mail->setAttributes($data);
      }else{
	$mail = $this->MailModel->newModel($data);
	$mail->status = STATUS_ENABLED;
	$mail->created_at = $this->app->data['_now_'];
      }
      $mail->updated_at = $this->app->data['_now_'];
      $mail->save();
      
      /* コミット */
      $this->db->commit();
    }catch(Exception $e){
      /* ロールバック */
      $this->db->rollback();

      /**/
      if($e instanceof Eln_Validation){
	/* エラー */
	$this->app->exportValidation($e, 'mail');
	return $this->viewForm();
      }else if($e instanceof Eln_Redirection){
	/* リダイレクト */
	throw $e;
      }else{
	/* 例外 */
	$this->app->writeLog('admin/mail/save', $e->getMessage());
	$this->redirect('default:admin/error.error');
      }
    }

    /**/
    $this->redirect('default:admin/mail.search');
  }
}
