<?php

/**/
include_once(__DIR__.'/vendor/autoload.php');

/**/
$mail = new \PHPMailer\PHPMailer\PHPMailer();
$mail->isSMTP();
$mail->SMTPDebug = 3;
$mail->SMTPAuth = false;
$mail->Host = MAIL_HOST;
$mail->SMTPSecure = MAIL_ENCRPT;
$mail->Port = SMTP_PORT;
$mail->CharSet = 'UTF-8';
$mail->Encoding = 'base64';
$mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
$mail->addAddress('mamo@sonosoft.com');
$mail->Subject = '[日誌] 送信テスト';
$mail->Body = '送信テスト'.PHP_EOL.'送信テスト';
$mail->isHTML(false);
if($mail->send()){
  echo '+ OK'.PHP_EOL;
}else{
  echo '- NG'.PHP_EOL;
}
