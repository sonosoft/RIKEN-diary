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
    $this->useModel('Page', 'Answer', 'ProjectDiary');

    /* データ */
    $diaries = $this->ProjectDiaryModel->collectDiaries($this->visit);
    if(($pages = $this->PageModel->load($diaries)) === null){
      $this->app->writeLog('work/send #1', 'failed to read data file.');
      $this->redirect('default:work.error');
    }
    if(isset($pages[$this->visit->page]) === false){
      $this->app->writeLog('work/send #2', 'invalid visit page.');
      $this->redirect('default:work.error');
    }
    if(($scale = $this->PageModel->getScale($pages[$this->visit->page])) !== false){
      $names = array(
	array($scale['header'].'_x', $scale['name'].'_x', false),
	array($scale['header'].'_y', $scale['name'].'_y', false),
      );
    }else{
      list($rows, $names, $values) = $this->PageModel->convert($pages[$this->visit->page]);
    }

    /* トランザクション */
    $this->db->begin();

    /**/
    try{
      /* 保存 */
      $answers = array();
      foreach($this->AnswerModel->collectByPage($this->user, $this->visit, $this->visit->page) as $record){
	$answers[$record->name] = $record;
      }
      /**/
      foreach($names as $entry){
	$this->saveAnswer($answers, $entry);
      }
      
      /* ページ */
      $direction = $this->app->readRequest('answer.direction', 0);
      if($direction == DIRECTION_NEXT){
	/**/
	$indexes = $this->PageModel->collectIndexes($pages);
	$curr = -1;
	foreach($indexes as $i=>$index){
	  if($index == $this->visit->page){
	    $curr = $i;
	    break;
	  }
	}
	if($curr < 0){
	  /* エラー */
	  $this->app->writeLog('work/send #3', 'invalid visit page.');
	  $this->redirect('default:work.error');
	}else if(isset($indexes[$curr + 1]) === false){
	  /* 終了 */
	  $this->visit->finished_at = $this->app->data['_now_'];
	}else{
	  /* 次のページ */
	  $this->visit->target = $pages[$indexes[$curr + 1]]['target'];
	  $this->visit->page = $indexes[$curr + 1];
	}
      }else if($direction == DIRECTION_PREV){
	/**/
	$indexes = $this->PageModel->collectIndexes($pages);
	$curr = -1;
	foreach($indexes as $i=>$index){
	  if($index == $this->visit->page){
	    $curr = $i;
	    break;
	  }
	}
	if($curr < 0){
	  /* エラー */
	  $this->app->writeLog('work/send #3', 'invalid visit page.');
	  $this->redirect('default:work.error');
	}else if(isset($indexes[$curr - 1]) === false){
	  /* エラー */
	  $this->app->writeLog('work/send #3', 'invalid visit page.');
	  $this->redirect('default:work.error');
	}else{
	  /* 前のページ */
	  $this->visit->page = $indexes[$curr - 1];
	}
      }else{
	/* エラー */
	$this->app->writeLog('work/send #4', 'invalid page direction.');
	$this->redirect('default:work.error');
      }
      $this->visit->accessed_at = $this->app->data['_now_'];
      $this->visit->save();

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
    if($this->visit->finished_at !== null){
      $this->redirect('default:work.finish');
    }
    $this->redirect('default:work.page');
  }

  /* ===== ===== */

  private function saveAnswer($answers, $item){
    /**/
    $value = $this->app->readRequest('answer.'.$item[1]);
    var_dump($item[1]);
    var_dump($value);
    $listed = 0;
    if(is_array($value)){
      if(empty($value)){
	$value;
      }else{
	$value = implode(',', $value);
      }
      $listed = 1;
    }
    if(isset($answers[$item[1]])){
      $answer = $answers[$item[1]];
    }else{
      $answer = $this->AnswerModel->newModel();
      $answer->user_id = $this->user->id;
      $answer->visit_id = $this->visit->id;
      $answer->page = $this->visit->page;
    }
    $answer->header = $item[0];
    $answer->name = $item[1];
    $answer->value = $value;
    $answer->listed = $listed;
    $answer->answered_at = $this->app->data['_now_'];
    $answer->save();
  }
}
