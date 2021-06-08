<?php

/**/
include_once(__DIR__.'/vendor/autoload.php');
include_once(__DIR__.'/script-lib.inc');


/*****/
if(function_exists('bindtextdomain') === false){
  function bindtextdomain($domain, $directory)
  {
  }
    }
if(function_exists('textdomain') === false){
  function textdomain($domain)
  {
  }
    }
if(function_exists('_') === false){
  function _($str)
  {
    return $str;
  }
}
ini_set('display_errors', true);
/*****/


/* アプリケーション */
$app = getApp();

/**/
echo '<<< start at: '.strftime('%Y/%m/%d %H:%M:%S').PHP_EOL;

/**/
try{
  /**/
  $title = 'テストメール';
  $body
    = 'テストメール'.PHP_EOL
    . 'タグ<strong>強調</strong>'.PHP_EOL
    . 'タグ<span style="color:red;">赤</span>'
    . 'タグ<span style="color:blue;">青</span>'.PHP_EOL;

  /**/
  $mail = new \PHPMailer\PHPMailer\PHPMailer();
  $mail->isSMTP();
  $mail->Host = MAIL_HOST;
  $mail->SMTPSecure = MAIL_ENCRPT;
  $mail->Port = SMTP_PORT;
  if(defined('SMTP_USER') || defined('SMTP_PASSWORD')){
    if(defined('SMTP_USER')){
      $mail->Username = SMTP_USER;
    }
    if(defined('SMTP_PASSWORD')){
      $mail->Password = SMTP_PASSWORD;
    }
    $mail->SMTPAuth = true;
  }else{
    $mail->SMTPAuth = false;
  }
  //$mail->SMTPDebug = 2;
  $mail->CharSet = 'UTF-8';
  $mail->Encoding = 'base64';
  $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
  $mail->addAddress('mamoru.misono@gmail.com');
  $mail->addAddress('s-presso@ezweb.ne.jp');
  $mail->Subject = $title;
  $mail->Body = nl2br($body);
  $mail->AltBody = strip_tags($body);
  $mail->isHTML(true);
  if($mail->send()){
    echo '+ OK'.PHP_EOL;
  }else{
    echo '- NG'.PHP_EOL;
  }
}catch(Exception $e){
  /* エラー */
  echo '***** ERROR ***** ['.date('Y/m/d H:i:s').'] *****'.PHP_EOL;
  echo $e->getMessage().PHP_EOL;
  echo '----- ----- ----- ----- -----'.PHP_EOL;
  echo debug_print_backtrace();
  echo '----- ----- ----- ----- -----'.PHP_EOL;
  echo PHP_EOL;
}

/**/
echo '>>> finished at: '.strftime('%Y/%m/%d %H:%M:%S').PHP_EOL;
