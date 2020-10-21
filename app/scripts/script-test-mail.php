<?php

include_once(__DIR__.'/vendor/autoload.php');

$mail = new \PHPMailer\PHPMailer\PHPMailer();
var_dump($mail);

$text = 'しましたか？【日誌|午前|摂取|2020/10/16】【】です。';

if(preg_match('/【日誌\|(起床時|午前|午後|就寝時)\|(入力|摂取)\|([\/0-9]+)】/', $text, $matches)){
  var_dump($matches);
}