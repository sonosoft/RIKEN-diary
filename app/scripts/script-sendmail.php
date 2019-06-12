<?php

/**/
include_once(__DIR__.'/vendor/autoload.php');
include_once(__DIR__.'/script-lib.inc');

function _toSend($mail, $project){
  global $now;
  global $today;

  // schedule.
  if(($schedule = json_decode($mail->schedule, true)) === false){
    return false;
  }
  
  // date.
  switch($schedule['flag']){
  case MAIL_BEFORE:
    if($today->getTime() != $project->from_date->getTime() - $schedule['before'] * 86400){
      return false;
    }
    break;
  case MAIL_AFTER:
    if($today->getTime() != $project->to_date->getTime() + $schedule['after'] * 86400){
      return false;
    }
    break;
  case MAIL_DURING:
    if($today->getTime() < $project->from_date->getTime() || $today->getTime() > $project->to_date->getTime()){
      return false;
    }
    break;
  case MAIL_DATE:
    if($today->getTime() != strtotime($schedule['date'])){
      return false;
    }
    break;
  }

  // time.
  $nowT = $now->hour * 100 + $now->minute;
  foreach($schedule['times'] as $time){
    if($nowT >= intval($time) && $nowT < intval($time) + 15){
      return true;
    }
  }

  //
  return false;
}

function _send($mail, $project, $user){
  /**/
  global $mailModel;

  /**/
  $title = $mailModel->replace($mail->title, $project, $user);
  $body = $mailModel->replace($mail->body, $project, $user);
  /**/
  $mail = new PHPMailer();
  $mail->isSMTP();
  $mail->SMTPAuth = false;
  $mail->Host = MAIL_HOST;
  $mail->SMTPSecure = MAIL_ENCRPT;
  $mail->Port = SMTP_PORT;
  $mail->CharSet = 'UTF-8';
  $mail->Encoding = 'base64';
  $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
  $mail->addAddress($user->email);
  $mail->Subject = $title;
  $mail->Body = $body;
  $mail->isHTML(false);
  if($mail->send()){
    echo '+ OK: '.$user->email.PHP_EOL;
  }else{
    echo '- NG: '.$user->email.PHP_EOL;
  }
}

/* アプリケーション */
$app = getApp();

/**/
echo '<<< start at: '.strftime('%Y/%m/%d %H:%M:%S').PHP_EOL;

/**/
try{
  /* 現在日時 */
  $now = Eln_Date::now();
  $today = Eln_Date::today();

  /* データベース接続 */
  $db = Eln_Database::setCurrentDatabase('database');
  $db->open();

  /* モデル */
  $projectModel = getModel('Project');
  $projectUserModel = getModel('ProjectUser');
  $mailModel = getModel('Mail');

  /* 有効なプロジェクト一覧 */
  $projectIds = array();
  $rsrc = $db->query('SELECT id FROM project WHERE status = :enabled', array('enabled'=>STATUS_ENABLED));
  while(($row = $rsrc->fetch_row()) !== null){
    $projectIds[] = $row[0];
  }
  foreach($projectIds as $projectId){
    /* プロジェクト+メール */
    $project = $projectModel->one(
      array('joins'=>array('mails'=>'mail'), 'where'=>'[id] = :id AND mails_mail.status = :enabled'),
      array('id'=>$projectId, 'enabled'=>STATUS_ENABLED)
    );
    if($project !== null){
      /* メール送信スケジュールチェック */
      foreach($project->mails as $mail){
	if(_toSend($mail->mail, $project)){
	  /* 送信 */
	  echo '['.$project->title.'] <'.$mail->mail->code.'>'.PHP_EOL;
	  foreach($projectUserModel->getByProject($project->id) as $user){
	    _send($mail->mail, $project, $user->user);
	  }
	}
      }
    }
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
