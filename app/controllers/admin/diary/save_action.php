<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/diary/save_action.php
 */


class AdminDiarySaveAction extends AdminDiaryController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Diary');
    $this->useValidator('DiaryForm');

    /* トランザクション */
    $this->db->begin();

    /**/
    try{
      /* 検証 */
      $data = $this->DiaryFormValidator->validate($this->app->readRequest('diary', array()));

      /* 保存 */
      if($data['id'] !== null){
	if(($diary = $this->DiaryModel->findById($data['id'])) === null){
	  $this->redirect('default:admin/error.invalid_access');
	}
	$diary->setAttributes($data);
      }else{
	$diary = $this->DiaryModel->newModel($data);
	$diary->status = STATUS_ENABLED;
	$diary->created_at = $this->app->data['_now_'];
      }
      $diary->updated_at = $this->app->data['_now_'];
      $diary->save();
      /**/
      if(isset($_FILES['file']['error']) && $_FILES['file']['error'] == UPLOAD_ERR_OK){
	$this->app->createDirectory(dirname($diary->path));
	move_uploaded_file($_FILES['file']['tmp_name'], $diary->path);
      }

      /* コミット */
      $this->db->commit();
    }catch(Exception $e){
      /* ロールバック */
      $this->db->rollback();

      /**/
      if($e instanceof Eln_Validation){
	/* エラー */
	$this->app->exportValidation($e, 'diary');
	return $this->viewForm();
      }else if($e instanceof Eln_Redirection){
	/* リダイレクト */
	throw $e;
      }else{
	/* 例外 */
	$this->app->writeLog('admin/diary/save', $e->getMessage());
	$this->redirect('default:admin/error.unexpected');
      }
    }

    /**/
    $this->redirect('default:admin/diary.search');
  }
}
