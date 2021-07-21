<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/user/send_action.php
 */


class AdminUserSendAction extends AdminUserController {
  /*
   * アクション
   */
  public function action(){
    /**/
    include_once($this->app->projectFile('scripts/vendor/autoload.php'));

    /**/
    $this->useModel('User', 'Message');

    /* リクエスト */
    $ids = $this->app->readRequest('message.users', array());
    $title = $this->app->readRequest('message.title', array());
    $body = $this->app->readRequest('message.body', array());
    /**/
    if(empty($ids) === false && is_array($ids)){
      $users = $this->UserModel->all(
	array('joins'=>'measurement', 'where'=>'[id] IN :ids AND [deleted_at] IS NULL'),
	array('ids'=>$ids)
      );
      foreach($users as $user){
	$title = $this->MessageModel->replace($title, $user, $user->measurement);
	$body = $this->MessageModel->replace($body, $user, $user->measurement);
	/**/
	$mail = new PHPMailer();
	$mail->isSMTP();
	$mail->SMTPAuth = false;
	$mail->Host = MAIL_HOST;
	$mail->SMTPSecure = MAIL_ENCRPT;
	$mail->Port = SMTP_PORT;
	$mail->CharSet = "UTF-8";
	$mail->Encoding = "base64";
	$mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
	$mail->addAddress($user->email);
	if($user->email_alt !== null){
	  $mail->addAddress($user->email_alt);
	}
	$mail->Subject = $title;
	$mail->isHTML(false);
	$mail->Body = $body;
	$mail->send();
	/*
	$this->MailModule->reset();
	$this->MailModule->setCharset('ISO-2022-JP');
	$this->MailModule->setHeaderEncoding('B');
	$this->MailModule->setSender('rch_he@ml.riken.jp');
	$this->MailModule->setRecipient('to', $user->email);
	$this->MailModule->setSubject($title);
	$this->MailModule->setBody($body);
	$this->MailModule->send();
	*/
      }
    }

    /**/
    $this->redirect('default:admin/user.receipt');
  }
}
