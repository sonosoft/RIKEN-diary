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
    $this->useModel('Project', 'User', 'Answer');
    $this->useValidator('AnswerSearch');

    /* 検索条件検証 */
    $this->app->data['answer_search'] = $this->AnswerSearchValidator->validate($this->app->data['answer_search']);

    /* データ初期化 */
    $this->app->data['dates'] = array();
    $this->app->data['users'] = array();

    /* リストオプション */
    $where = array();
    $validity = true;
    if($this->app->data['answer_search']['project_id'] !== null){
      $where[] = 'answer.project_id = :project_id';
    }else{
      $validity = false;
    }
    if($this->app->data['answer_search']['from'] !== null){
      $where[] = 'answer.recorded_on >= :from';
    }else{
      $validity = false;
    }
    if($this->app->data['answer_search']['to'] !== null){
      $where[] = 'answer.recorded_on <= :to';
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
	  'getup'=>array('data'=>array(), 'sum'=>0, 'score'=>0),
	  'gotobed'=>array('data'=>array(), 'sum'=>0, 'score'=>0),
	);
      }

      /* 日付 */
      $cnt = $this->app->data['answer_search']['from'];
      $dates = array();
      while($cnt->compare($this->app->data['answer_search']['to']) <= 0){
	foreach($users as $code=>$user){
	  $date = $cnt->format('%Y/%m/%d');
	  $dates[] = $date;
	  $users[$code]['getup']['data'][] = 0;
	  $users[$code]['gotobed']['data'][] = 0;
	}
	$cnt = new Eln_Date($cnt->getTime() + 86400);
      }
      if(empty($dates) === false){
	/* データ */
	$timings = array(TIMING_GETUP=>'getup', TIMING_GOTOBED=>'gotobed');
	$rsrc = $this->db->query(
	  'SELECT user.code, answer.recorded_on, answer.timing '.
	  'FROM answer '.
	  'LEFT JOIN user ON user.id = answer.user_id '.
	  'WHERE '.implode(' AND ', $where).' '.
	  'GROUP BY user.id, answer.recorded_on, answer.timing '.
	  'ORDER BY user.code ASC',
	  $this->app->data['answer_search']
	);
	while(($row = $rsrc->fetch_row()) !== null){
	  $index = intval((strtotime($row[1]) - $this->app->data['answer_search']['from']->getTime()) / 86400);
	  $users[$row[0]][$timings[$row[2]]]['data'][$index] = 1;
	}
	foreach($users as $code=>$user){
	  foreach($timings as $timing){
	    $sum = 0;
	    foreach($user[$timing]['data'] as $value){
	      $sum += intval($value);
	    }
	    $users[$code][$timing]['sum'] = $sum;
	    $users[$code][$timing]['score'] = intval(($sum * 100) / count($user[$timing]['data']));
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
    $header .= '"合計","合計/日数"';
    $header .= "\r\n";
    echo mb_convert_encoding($header, 'SJIS-win', 'UTF-8');

    /* データ */
    foreach($this->app->data['users'] as $user){
      foreach(array('getup'=>'起床日誌', 'gotobed'=>'就寝日誌') as $timing=>$label){
	$row  = '"'.$user['code'].'",';
	$row .= '"'.$label.'",';
	foreach($user[$timing]['data'] as $value){
	  $row .= '"'.$value.'",';
	}
	$row .= '"'.$user[$timing]['sum'].'","'.$user[$timing]['score'].'%"';
	$row .= "\r\n";
	echo mb_convert_encoding($row, 'SJIS-win', 'UTF-8');
      }
    }
  }
}
