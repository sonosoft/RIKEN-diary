<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/message/controller.php
 */


/*
 * コントローラ
 */
class AdminMessageController extends AdminController {
  /*
   * 一覧
   */
  protected function viewList(){
    /**/
    $this->useModel('User', 'Message');
    $this->useValidator('MessageSearch');

    /* 検索条件検証 */
    $this->app->data['message_search'] = $this->MessageSearchValidator->validate($this->app->data['message_search']);

    /* リストオプション */
    $where = array();
    if($this->app->data['message_search']['text_keys'] !== null){
      foreach($this->app->data['message_search']['text_keys'] as $key){
	$where[] = sprintf('(INSTR([code], :%s) != 0 OR INSTR([subject], :%s) != 0 OR INSTR([body], :%s) != 0)', $key, $key, $key);
      }
    }
    if($this->app->data['message_search']['from'] !== null){
      $where[] = 'DATE([sent_at]) >= :from';
    }
    if($this->app->data['message_search']['to'] !== null){
      $where[] = 'DATE([sent_at]) <= :to';
    }
    $where[] = '[status] = :enabled';
    /**/
    $options = array('where'=>implode(' AND ', $where));
    /**/
    $parameters = $this->app->data['message_search'];
    $parameters['enabled'] = STATUS_ENABLED;

    /**/
    if($this->app->data['message_search']['download']){
      /* ダウンロード */
      $this->_doDownload($options, $parameters);
    }else{
      /* 検索条件 */
      $options['order'] = '[code] DESC';
      $options['pageSize'] = 30;
      $options['indexSize'] = 10;
      $options['page'] = $this->app->data['message_search']['page'];
      switch($this->app->data['message_search']['order']){
      case 'i-a':
	$options['order'] = '[code] ASC';
	break;
      case 't-a':
	$options['order'] = '[subject] ASC';
	break;
      case 't-d':
	$options['order'] = '[subject] DESC';
	break;
      case 's-a':
	$options['order'] = '[sent_at] ASC';
	break;
      case 's-d':
	$options['order'] = '[sent_at] DESC';
	break;
      case 'f-a':
	$options['order'] = '[finished_at] ASC';
	break;
      case 'f-d':
	$options['order'] = '[finished_at] DESC';
	break;
      }
      
      /* 検索 */
      list($this->app->data['messages'], $this->app->data['paginator']) = $this->MessageModel->page($options, $parameters);
      /**/
      $this->app->data['message_search']['page'] = $this->app->data['paginator']->currentPage;
      $this->app->storeSession('message_search', $this->app->data['message_search']);
    }

    /**/
    return 'admin/message/list';
  }

  /*
   * フォーム
   */
  protected function viewForm(){
    /**/
    $this->useModel('User');

    /* 時間 */
    $this->app->data['hourChoices'] = array();
    foreach(range(0, 23) as $h){
      $this->app->data['hourChoices'][] = array('value'=>$h, 'label'=>sprintf('%02d', $h));
    }
    $this->app->data['minuteChoices'] = array();
    foreach(array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55) as $m){
      $this->app->data['minuteChoices'][] = array('value'=>$m, 'label'=>sprintf('%02d', $m));
    }

    /* 送信先 */
    if(($ids = json_decode($this->app->data['message']['destinations'], true)) !== null){
      $users = $this->UserModel->all(
	array('where'=>'[id] IN :ids AND [status] = :enabled'),
	array('ids'=>$ids, 'enabled'=>STATUS_ENABLED)
      );
      $this->app->data['users'] = $users;
    }

    /**/
    return 'admin/message/form';
  }

  /* ===== ===== */

  private function _doDownload($options, $parameters){
    /**/
    $users = array();
    
    /* 検索 */
    $list = array();
    foreach($this->MessageModel->all($options, $parameters) as $message){
      if(($destinations = json_decode($message->destinations, true)) !== null){
	if($message->finished_at !== null){
	  $finished_at = $message->finished_at->format('%Y/%m/%d %H:%M');
	}else{
	  $finished_at = '';
	}
	foreach($destinations as $destination){
	  if(isset($users[$destination]) === false){
	    $user = $this->UserModel->one(
	      array('where'=>'[id] = :id AND [status] = :enabled'),
	      array('id'=>$destination, 'enabled'=>STATUS_ENABLED)
	    );
	    if($user !== null){
	      $users[$destination] = $user->code;
	    }
	  }
	  if(isset($users[$destination])){
	    $list[] = array(
	      $users[$destination],
	      $message->code,
	      $message->subject,
	      $message->sent_at->format('%Y/%m/%d %H:%M'),
	      $finished_at,
	    );
	  }
	}
      }
    }
    usort($list, array($this, '_compare'));

    /* ダウンロード */
    header('Content-Type: text/csv; charset=shift_JIS');
    header('Content-Disposition: attachment;filename="'.strftime('messages_%Y%m%d_%H%M%S.csv').'"');
    header('Cache-Control: max-age=0');
    /**/
    $this->_printSJIS('"被験者ID","メールID","タイトル","送信予定日時","送信完了日時"'."\r\n");
    foreach($list as $entry){
      $line  = '"'.$entry[0].'",';
      $line .= '"'.$entry[1].'",';
      $line .= '"'.$entry[2].'",';
      $line .= '"'.$entry[3].'",';
      $line .= '"'.$entry[4].'"';
      $this->_printSJIS($line."\r\n");
    }
    
    /**/
    $this->direct();
  }

  private function _compare($a, $b){
    $uc = strcmp($a[0], $b[0]);
    if($uc < 0){
      return -1;
    }else if($uc > 0){
      return 1;
    }else{
      $mc = strcmp($a[1], $b[1]);
      if($mc < 0){
	return -1;
      }else if($mc > 0){
	return 1;
      }
    }
    return 0;
  }
  
  private function _printSJIS($str){
    echo mb_convert_encoding($str, 'SJIS-win', 'UTF-8');
  }
}
