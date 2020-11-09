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
  if($project !== null){
    $body = _finish($body, $project, $user);
  }
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

function _send_message($record, $project, $user){
  /**/
  global $mailModel;

  /**/
  $subject = $mailModel->replace($record->subject, $project, $user);
  $body = $mailModel->replace($record->body, $project, $user);
  if($project !== null){
    $body = _finish($body, $project, $user);
  }
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
  $mail->CharSet = 'UTF-8';
  $mail->Encoding = 'base64';
  $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
  $mail->addAddress($user->email);
  $mail->Subject = $subject;
  $mail->Body = $body;
  $mail->isHTML(false);
  echo 'MESSAGE['.$record->code.'] ';
  if($mail->send()){
    echo '+ OK: '.$user->email.PHP_EOL;
  }else{
    echo '- NG: '.$user->email.PHP_EOL;
  }
}

function _finish($text, $project, $user){
  /**/
  global $visitModel;
  global $answerModel;

  /**/
  while(true){
    if(preg_match('/【日誌\|(起床時|午前|午後|就寝時)\|(入力|摂取)\|([\/0-9]*)】/', $text, $matches)){
      $alt = '';
      switch($matches[1]){
      case '起床時':
	$timing = TIMING_GETUP;
	break;
      case '午前':
	$timing = TIMING_AM;
	break;
      case '午後':
	$timing = TIMING_PM;
	break;
      case '就寝時':
	$timing = TIMING_GOTOBED;
	break;
      }
      $type = $matches[2];
      if(empty($matches[3])){
	$date = Eln_Date::today();
      }else{
	$date = new Eln_date(strtotime($matches[3]));
      }
      if($date !== false){
	if($type == '入力'){
	  $ws = array(
	    '[user_id] = :user_id',
	    '[project_id] = :project_id',
	    '[visited_on] = :date',
	    '[timing] = :timing',
	    '[finished_at] IS NOT NULL',
	  );
	  $visit = $visitModel->one(
	    array('where'=>implode(' AND ', $ws)),
	    array('user_id'=>$user->id, 'project_id'=>$project->id, 'date'=>$date, 'timing'=>$timing)
	  );
	  if($visit !== null){
	    $alt = '【入力済】';
	  }else{
	    $alt = '【未回答】';
	  }
	}else{
	  if($timing == TIMING_AM){
	    $name = QUESTION_AM;
	  }else if($timing = TIMING_PM){
	    $name = QUESTION_PM;
	  }else{
	    $name = null;
	  }
	  if($name !== null){
	    $ws = array(
	      'visit.user_id = :user_id',
	      'visit.project_id = :project_id',
	      'visit.visited_on = :date',
	      'visit.timing = :timing',
	      '[name] = :name',
	    );
	    $answer = $answerModel->one(
	      array('joins'=>'visit', 'where'=>implode(' AND ', $ws)),
	      array('user_id'=>$user->id, 'project_id'=>$project->id, 'date'=>$date, 'timing'=>$timing, 'name'=>$name)
	    );
	    if($answer === null){
	      $alt = '【未回答】';
	    }else if($answer->value == 1){
	      $alt = '【はい】';
	    }else if($answer->value == 2){
	      $alt = '【いいえ】';
	    }
	  }
	}
      }
      $text = str_replace($matches[0], $alt, $text);
    }else{
      break;
    }
  }

  /**/
  return $text;
}

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
  $messageModel = getModel('Message');
  $userModel = getModel('User');
  $visitModel = getModel('Visit');
  $answerModel = getModel('Answer');

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

  /* 未送信メッセージ */
  $messages = $messageModel->all(
    array('where'=>'[started_at] IS NULL AND [status] = :enabled'),
    array('enabled'=>STATUS_ENABLED)
  );
  foreach($messages as $message){
    if($message->sent_at->compare($now) <= 0){
      /* 開始 */
      $db->begin();
      try{
	$message->started_at = $now;
	$message->save();
	$db->commit();
      }catch(Exception $e){
	$db->rollback();
	echo '!! MESSAGE['.$message->code.'] Failed to update start time.'.PHP_EOL;
      }
      if($message->started_at !== null){
	/* 送信 */
	if(($destinations = json_decode($message->destinations, true)) !== null){
	  foreach($destinations as $destination){
	    $user = $userModel->one(
	      array('where'=>'[id] = :id AND [status] = :enabled'),
	      array('id'=>$destination, 'enabled'=>STATUS_ENABLED)
	    );
	    if($user !== null){
	      $sent = false;
	      foreach($projectUserModel->getByUser($user->id) as $project){
		_send_message($message, $project->project, $user);
		$sent = true;
	      }
	      if($sent === false){
		_send_message($message, null, $user);
	      }
	    }
	  }
	}
	
	/* 終了 */
	$db->begin();
	try{
	  $message->finished_at = $now;
	  $message->save();
	  $db->commit();
	}catch(Exception $e){
	  $db->rollback();
	  echo '!! MESSAGE['.$message->code.'] Failed to update finish time'.PHP_EOL;
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
