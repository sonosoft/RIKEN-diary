<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * work/start_action.php
 */


class WorkStartAction extends WorkController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Page', 'Inquiry', 'System');

    /**/
    $this->db->begin();

    /**/
    try{
      /* セッション */
      if(($pages = $this->PageModel->load($system)) === null){
	$this->app->writeLog('work/start #1', 'failed to read profile data file.');
	$this->redirect('default:work.error');
      }
      $indexes = $this->PageModel->collectIndexes($pages);
      if(empty($indexes)){
	$this->app->writeLog('work/start #1', 'failed to get page indexes.');
	$this->redirect('default:work.error');
      }
      $session = $this->SessionModel->newModel();
      $session->system_id = $system->id;
      $session->user_id = $this->user->id;
      $session->target = $pages[$indexes[0]]['target'];
      $session->page = $indexes[0];
      $session->started_at = $this->app->data['_now_'];
      $session->accessed_at = $this->app->data['_now_'];
      $session->save();

      /* コミット */
      $this->db->commit();

      /**/
      $this->app->storeSession('work_data.session_id', $session->id);
    }catch(Exception $e){
      /* ロールバック */
      $this->db->rollback();

      /**/
      if($e instanceof Eln_Redirection){
	/* リダイレクト */
	throw $e;
      }else{
	/* エラー */
	$this->app->writeLog('work/start', $e->getMessage());
	$this->redirect('default:work.error');
      }
    }
    
    /**/
    $this->redirect('default:work.page');
  }
}
