<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/project/download_action.php
 */


class AdminProjectDownloadAction extends AdminController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('ProjectDiary', 'Page', 'Visit', 'Answer');

    /* リクエスト */
    $id = $this->app->readRequest('download.id', 0);
    $fromS = $this->app->readRequest('download.from');
    if(empty($fromS) === false){
      $from = strtotime($fromS);
    }else{
      $from = false;
    }
    $toS = $this->app->readRequest('download.to');
    if(empty($toS) === false){
      $to = strtotime($toS);
    }else{
      $to = false;
    }
    
    /* ヘッダ */
    header('Content-Type: text/csv; charset=shift_JIS');
    header('Content-Disposition: attachment;filename="'.strftime('download_%Y%m%d_%H%M%S.csv').'"');
    header('Cache-Control: max-age=0');
    
    /* 日誌 */
    $diaries = $this->ProjectDiaryModel->getByProject($id);
    if(($pages = $this->PageModel->load($diaries)) !== null){
      $where = array();
      $where[] = 'visit.project_id = :id';
      $where[] = 'visit.finished_at IS NOT NULL';
      if($from){
	$where[] = 'DATE(visit.finished_at) >= :from';
      }
      if($to){
	$where[] = 'DATE(visit.finished_at) <= :to';
      }

      /* ヘッダ */
      $headers = array(array('"入力日時"', '"被験者"', '"性別"', '"生年月日"'), array('"DATETIME"', '"USER"', '"SEX"', '"BIRTHDAY"'));
      $names = array();
      foreach($pages as $index=>$page){
	list(, $ns,) = $this->PageModel->convert($page);
	foreach($ns as $n){
	  $names[] = $n;
	}
      }
      foreach($names as $name){
	if(!$name[2]){
	  $headers[0][] = '"'.$name[0].'"';
	  $headers[1][] = '"'.$name[1].'"';
	}
      }
      $this->printSJIS(implode(',', $headers[0])."\r\n");
      $this->printSJIS(implode(',', $headers[1])."\r\n");
      
      /* ボディ */
      $visits = $this->db->query(
	'SELECT'.
	' visit.id, visit.finished_at, user.id, user.code, user.sex, user.birthday'.
	'FROM visit '.
	'LEFT OUTER JOIN user ON user.id = visit.user_id '.
	'WHERE '.implode(' AND ', $where).' '.
	'ORDER BY session.finished_at DESC',
	array('id'=>$id, 'from'=>$from, 'to'=>$to)
      );
      while(($visit = $visits->fetch_row()) !== null){
	$this->printSJIS('"'.$visit[1].'","'.$visit[3].'",');
	if(intval($visit[4]) == SEX_MALE){
	  $this->printSJIS('"男性"');
	}else if(intval($visit[4]) == SEX_FEMALE){
	  $this->printSJIS('"女性"');
	}else{
	  $this->printSJIS('""');
	}
	$this->printSJIS(',"'.$visit[5].'"');
	/**/
	$answers = array();
	$records = $this->InquiryModel->all(
	  array('where'=>'[user_id] = :user_id AND [visit_id] = :visit_id'),
	  array('user_id'=>$visit[2], 'visit_id'=>$visit[0])
	);
	foreach($records as $record){
	  $answers[$record->name] = $record->value;
	}
	foreach($names as $name){
	  if(!$name[2]){
	    if(isset($answers[$name[1]])){
	      $this->printSJIS(',"'.$answers[$name[1]].'"');
	    }else{
	      $this->printSJIS(',"null"');
	    }
	  }
	}
	$this->printSJIS("\r\n");
      }
    }

    /**/
    $this->direct();
  }

  /* ===== ===== */
  
  private function printSJIS($str){
    echo mb_convert_encoding($str, 'SJIS-win', 'UTF-8');
  }
}
