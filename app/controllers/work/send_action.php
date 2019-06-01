<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * work/send_action.php
 */


class WorkSendAction extends WorkController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Page', 'Inquiry');

    /* データ */
    if($this->session === null){
      $this->app->writeLog('work/send #1', 'invalid session.');
      $this->redirect('default:work.error');
    }
    if(($pages = $this->PageModel->load($this->session->system)) === null){
      $this->app->writeLog('work/send #1', 'failed to load pages.');
      $this->redirect('default:work.error');
    }
    if(isset($pages[$this->session->page]) === false){
      $this->app->writeLog('work/send #1', 'invalid session page.');
      $this->redirect('default:work.error');
    }
    list($rows, $names, $values) = $this->PageModel->convert($pages[$this->session->page]);

    /* トランザクション */
    $this->db->begin();

    /**/
    try{
      /* 保存 */
      $inquiries = array();
      foreach($this->InquiryModel->collectByPage($this->user, $this->session, $this->session->page) as $record){
	$inquiries[$record->name] = $record;
      }
      /**/
      $sequence = 1;
      foreach($names as $entry){
	$this->saveInquiry($inquiries, $sequence, $entry);
	++ $sequence;
      }
      
      /* ページ */
      $direction = $this->app->readRequest('inquiry.direction', 0);
      if($direction == DIRECTION_NEXT){
	/**/
	$indexes = $this->PageModel->collectIndexes($pages);
	$curr = -1;
	foreach($indexes as $i=>$index){
	  if($index == $this->session->page){
	    $curr = $i;
	    break;
	  }
	}
	if($curr < 0){
	  /* エラー */
	  $this->app->writeLog('work/send #2', 'invalid session page.');
	  $this->redirect('default:work.error');
	}else if(isset($indexes[$curr + 1]) === false){
	  /* 終了 */
	  $this->session->finished_at = $this->app->data['_now_'];
	}else{
	  /* 次のページ */
	  $this->session->target = $pages[$indexes[$curr + 1]]['target'];
	  $this->session->page = $indexes[$curr + 1];
	}
      }else if($direction == DIRECTION_PREV){
	/**/
	$indexes = $this->PageModel->collectIndexes($pages);
	$curr = -1;
	foreach($indexes as $i=>$index){
	  if($index == $this->session->page){
	    $curr = $i;
	    break;
	  }
	}
	if($curr < 0){
	  /* エラー */
	  $this->app->writeLog('work/send #3', 'invalid session page.');
	  $this->redirect('default:work.error');
	}else if(isset($indexes[$curr - 1]) === false){
	  /* エラー */
	  $this->app->writeLog('work/send #3', 'invalid session page.');
	  $this->redirect('default:work.error');
	}else{
	  /* 前のページ */
	  $this->session->target = $pages[$indexes[$curr - 1]]['target'];
	  $this->session->page = $indexes[$curr - 1];
	}
      }else{
	/* エラー */
	$this->app->writeLog('work/send #4', 'invalid page direction.');
	$this->redirect('default:work.error');
      }
      $this->session->accessed_at = $this->app->data['_now_'];
      $this->session->save();

      /* コミット */
      $this->db->commit();
    }catch(Exception $e){
      /* ロールバック */
      $this->db->rollback();

      /**/
      if($e instanceof Eln_Redirection){
	/* リダイレクト */
	throw $e;
      }else{
	/* エラー */
	$this->app->writeLog('work/send', $e->getMessage());
	$this->redirect('default:work.error');
      }
    }
    
    /**/
    if($this->session->finished_at !== null){
      if($this->user->code !== null && $this->user->token !== null){
	$this->db->begin();
	try{
	  $this->db->query(
	    'UPDATE applicants SET isSentQuestionnaire = 1 WHERE userID = :code AND randomString = :token',
	    array('code'=>$this->user->code, 'token'=>$this->user->token)
	  );
	  $this->db->commit();
	}catch(Exception $e){
	  $this->db->rollback();
	  $this->app->writeLog('work/send', $e->getMessage());
	}
      }
      $this->redirect('default:work.finish');
    }
    $this->redirect('default:work.page');
  }

  /* ===== ===== */

  private function saveInquiry($inquiries, $sequence, $item){
    /**/
    $value = $this->app->readRequest('inquiry.'.$item[1]);
    $listed = 0;
    if(is_array($value)){
      if(empty($value)){
	$value;
      }else{
	$value = implode(',', $value);
      }
      $listed = 1;
    }
    if(isset($inquiries[$item[1]])){
      $inquiry = $inquiries[$item[1]];
    }else{
      $inquiry = $this->InquiryModel->newModel();
      $inquiry->user_id = $this->user->id;
      $inquiry->session_id = $this->session->id;
      $inquiry->target = $this->session->target;
      $inquiry->page = $this->session->page;
    }
    $inquiry->sequence = $sequence;
    $inquiry->header = $item[0];
    $inquiry->name = $item[1];
    $inquiry->value = $value;
    $inquiry->listed = $listed;
    $inquiry->registered_at = $this->app->data['_now_'];
    $inquiry->save();
  }
}
