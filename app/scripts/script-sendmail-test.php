<?php

/**/
include_once(__DIR__.'/vendor/autoload.php');
include_once(__DIR__.'/script-lib.inc');


function _send_message($record, $project, $user){
  /**/
  global $mailModel;

  /**/
  $subject = $mailModel->replace($record->subject, $project, $user);
  $body = $mailModel->replace($record->body, $project, $user);
  if($project !== null){
    $body = _finish($body, $project, $user);
  }
  //echo $subject.PHP_EOL.PHP_EOL;
  //echo $body.PHP_EOL.PHP_EOL;
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
	$date = Eln_date(strtotime($matches[3]));
      }
      echo $timing.','.$type.','.$date.PHP_EOL;
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

/* アプリケーション */
$app = getApp();

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

  $message = $messageModel->one(
    array('where'=>'[code] = :code'),
    array('code'=>'AS100')
  );
  echo $message->code.PHP_EOL;

  /* テスト */
  if(($destinations = json_decode($message->destinations, true)) !== null){
    foreach($destinations as $destination){
      $user = $userModel->one(
	array('where'=>'[id] = :id AND [status] = :enabled'),
	array('id'=>$destination, 'enabled'=>STATUS_ENABLED)
      );
      if($user !== null){
	echo $user->id.','.$user->code.PHP_EOL;
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
}catch(Exception $e){
  /* エラー */
  echo '***** ERROR ***** ['.date('Y/m/d H:i:s').'] *****'.PHP_EOL;
  echo $e->getMessage().PHP_EOL;
  echo '----- ----- ----- ----- -----'.PHP_EOL;
  echo debug_print_backtrace();
  echo '----- ----- ----- ----- -----'.PHP_EOL;
  echo PHP_EOL;
}
