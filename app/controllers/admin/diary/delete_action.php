<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/diary/delete_action.php
 */


class AdminDiaryDeleteAction extends AdminDiaryController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Diary');

    /* トランザクション */
    $this->db->begin();

    /**/
    try{
      /* 削除 */
      if(($diary = $this->DiaryModel->findById($this->app->route['id'])) === null){
	$this->redirect('default:admin/error.invalid_access');
      }
      $diary->status = STATUS_DISABLED;
      $diary->deleted_at = $this->app->data['_now_'];
      $diary->save();

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
	/* 例外 */
	$this->app->writeLog('admin/diary/delete', $e->getMessage());
	$this->redirect('default:admin/error.unexpected');
      }
    }

    /**/
    $this->redirect('default:admin/diary.search');
  }
}
