<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/answer/controller.php
 */


/*
 * コントローラ
 */
class AdminAnswerController extends AdminController {
  /*
   * 一覧
   */
  protected function viewList(){
    /**/
    $this->useModel('Project', 'User', 'Visit', 'Answer');
    $this->useValidator('AnswerSearch');

    /* 検索条件検証 */
    $this->app->data['answer_search'] = $this->AnswerSearchValidator->validate($this->app->data['answer_search']);

    /* データ初期化 */
    $this->app->data['dates'] = array();
    $this->app->data['users'] = array();

    /* リストオプション */
    $where = array('visit.finished_at IS NOT NULL');
    $validity = true;
    if($this->app->data['answer_search']['project_id'] !== null){
      $where[] = 'visit.project_id = :project_id';
    }else{
      $validity = false;
    }
    if($this->app->data['answer_search']['from'] !== null){
      $where[] = 'DATE(visit.started_at) >= :from';
    }else{
      $validity = false;
    }
    if($this->app->data['answer_search']['to'] !== null){
      $where[] = 'DATE(visit.started_at) <= :to';
    }else{
      $validity = false;
    }
    if($validity){
      /* ユーザ */
      $rsrc = $this->db->query(
	'SELECT user.code '.
	'FROM user '.
	'LEFT JOIN project_user ON project_user.user_id = user.id '.
	'WHERE project_user.project_id = :project_id '.
	'ORDER BY user.code',
	$this->app->data['answer_search']
      );
      $users = array();
      while(($row = $rsrc->fetch_row()) !== null){
	$users[$row[0]] = array(
	  'code'=>$row[0],
	  'getup'=>array('data'=>array(), 'sum1'=>0, 'sum2'=>0, 'score1'=>0, 'score2'=>0),
	  'am'=>array('data'=>array(), 'sum1'=>0, 'sum2'=>0, 'score1'=>0, 'score2'=>0),
	  'pm'=>array('data'=>array(), 'sum1'=>0, 'sum2'=>0, 'score1'=>0, 'score2'=>0),
	  'gotobed'=>array('data'=>array(), 'sum1'=>0, 'sum2'=>0, 'score1'=>0, 'score2'=>0),
	);
      }

      /* 日付 */
      $cnt = $this->app->data['answer_search']['from'];
      $dates = array();
      while($cnt->compare($this->app->data['answer_search']['to']) <= 0){
	$date = $cnt->format('%Y/%m/%d');
	$dates[] = $date;
	foreach($users as $code=>$user){
	  $users[$code]['getup']['data'][] = 0;
	  $users[$code]['am']['data'][] = 0;
	  $users[$code]['pm']['data'][] = 0;
	  $users[$code]['gotobed']['data'][] = 0;
	}
	$cnt = new Eln_Date($cnt->getTime() + 86400);
      }
      if(empty($dates) === false){
	/* データ */
	$timings = array(TIMING_GETUP=>'getup', TIMING_AM=>'am', TIMING_PM=>'pm', TIMING_GOTOBED=>'gotobed');
	$rsrc = $this->db->query(
	  'SELECT user.code, visit.timing, DATE(visit.started_at), visit.id '.
	  'FROM visit '.
	  'LEFT JOIN user ON user.id = visit.user_id '.
	  'WHERE '.implode(' AND ', $where).' '.
	  'ORDER BY user.code ASC',
	  $this->app->data['answer_search']
	);
	while(($row = $rsrc->fetch_row()) !== null){
	  $index = intval((strtotime($row[2]) - $this->app->data['answer_search']['from']->getTime()) / 86400);
	  if($users[$row[0]][$timings[$row[1]]]['data'][$index] == 0){
	    $users[$row[0]][$timings[$row[1]]]['data'][$index] = 1;
	  }
	  if($row[1] == TIMING_AM || $row[1] == TIMING_PM){
	    $answer = $this->AnswerModel->one(
	      array('where'=>'[visit_id] = :visit_id AND [name] = :name'),
	      array('visit_id'=>$row[3], 'name'=>(($row[3] == TIMING_AM) ? QUESTION_AM : QUESTION_PM))
	    );
	    if($answer->value == 1){
	      $users[$row[0]][$timings[$row[1]]]['data'][$index] = 2;
	    }
	  }
	}
	foreach($users as $code=>$user){
	  foreach($timings as $timing){
	    $sum1 = 0;
	    $sum2 = 0;
	    foreach($user[$timing]['data'] as $value){
	      if(intval($value)){
		++ $sum1;
		if(intval($value) == 2){
		  ++ $sum2;
		}
	      }
	    }
	    $users[$code][$timing]['sum1'] = $sum1;
	    $users[$code][$timing]['sum2'] = $sum2;
	    $users[$code][$timing]['score1'] = intval(($sum1 * 100) / count($user[$timing]['data']));
	    $users[$code][$timing]['score2'] = intval(($sum2 * 100) / count($user[$timing]['data']));
	  }
	}
	/**/
	$this->app->data['dates'] = $dates;
	$this->app->data['users'] = $users;
      }
    }

    /* ダウンロード */
    if($this->app->data['answer_search']['download']){
      $this->_download();
      $this->direct();
    }
    
    /* 選択肢 */
    $this->app->data['projectChoices'] = $this->ProjectModel->collectChoices();

    /**/
    return 'admin/answer/list';
  }

  private function _download(){
    /* ヘッダ */
    header('Content-Type: text/csv; charset=shift_JIS');
    header('Content-Disposition: attachment;filename="'.strftime('daycheck_%Y%m%d_%H%M%S.csv').'"');
    header('Cache-Control: max-age=0');

    /* データヘッダ */
    $header  = '"ID","日誌",';
    foreach($this->app->data['dates'] as $date){
      $header .= '"'.$date.'",';
    }
    $header .= '"入力回数","摂取回数","入力%","摂取%"';
    $header .= "\r\n";
    echo mb_convert_encoding($header, 'SJIS-win', 'UTF-8');

    /* データ */
    foreach($this->app->data['users'] as $user){
      foreach(array('getup'=>'起床時', 'am'=>'午前', 'pm'=>'午後', 'gotobed'=>'就寝時') as $timing=>$label){
	$row  = '"'.$user['code'].'",';
	$row .= '"'.$label.'",';
	foreach($user[$timing]['data'] as $value){
	  $row .= '"'.$value.'",';
	}
	if($timing == 'am' || $timing == 'pm'){
	  $row .= '"'.$user[$timing]['sum1'].'","'.$user[$timing]['sum2'].'","'.$user[$timing]['score1'].'%","'.$user[$timing]['score2'].'%"';
	}else{
	  $row .= '"'.$user[$timing]['sum1'].'","-","'.$user[$timing]['score1'].'%","-"';
	}
	$row .= "\r\n";
	echo mb_convert_encoding($row, 'SJIS-win', 'UTF-8');
      }
    }
  }
}
