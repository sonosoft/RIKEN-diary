<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/applicant/send_action.php
 */


class AdminApplicantSendAction extends AdminApplicantController {
  /*
   * アクション
   */
  public function action(){
    /**/
    include_once($this->app->projectFile('scripts/vendor/autoload.php'));

    /**/
    $this->useModel('Applicant', 'Message');

    /* リクエスト */
    $ids = $this->app->readRequest('message.applicants', array());
    $title = $this->app->readRequest('message.title', array());
    $body = $this->app->readRequest('message.body', array());
    /**/
    if(empty($ids) === false && is_array($ids)){
      $applicants = $this->ApplicantModel->all(
	array('joins'=>'measurement', 'where'=>'[id] IN :ids AND [deleted_at] IS NULL'),
	array('ids'=>$ids)
      );
      foreach($applicants as $applicant){
	$title = $this->MessageModel->replace($title, $applicant, $applicant->measurement);
	$body = $this->MessageModel->replace($body, $applicant, $applicant->measurement);
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
	$mail->addAddress($applicant->email);
	$mail->Subject = $title;
	$mail->isHTML(false);
	$mail->Body = $body;
	$mail->send();
	/*
	$this->MailModule->reset();
	$this->MailModule->setCharset('ISO-2022-JP');
	$this->MailModule->setHeaderEncoding('B');
	$this->MailModule->setSender('rch_he@ml.riken.jp');
	$this->MailModule->setRecipient('to', $applicant->email);
	$this->MailModule->setSubject($title);
	$this->MailModule->setBody($body);
	$this->MailModule->send();
	*/
      }
    }

    /**/
    $this->redirect('default:admin/applicant.receipt');
  }
}
