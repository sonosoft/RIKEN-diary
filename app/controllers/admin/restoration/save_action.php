<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/restoration/save_action.php
 */


class AdminRestorationSaveAction extends AdminController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Restoration');
    $this->useValidator('RestorationForm');

    /* トランザクション */
    $this->db->begin();

    /**/
    try{
      /* 検証 */
      $data = $this->RestorationFormValidator->validate($_POST);

      /* 保存 */
      $restoration = $this->RestorationModel->newModel($data);
      $restoration->token = $this->RestorationModel->generateToken();
      $restoration->status = STATUS_ENABLED;
      $restoration->created_at = $this->app->data['_now_'];
      $restoration->updated_at = $this->app->data['_now_'];
      $restoration->save();

      /* コミット */
      $this->db->commit();
    }catch(Exception $e){
      /* ロールバック */
      $this->db->rollback();

      if($e instanceof Eln_Validation){
	/* エラー */
	$this->redirect('default:admin/error.invalid_access');
      }else{
	/* 例外 */
	$this->app->writeLog('admin/restoration/save', $e->getMessage());
	$this->redirect('default:admin/error.unexpected');
      }
    }

    /**/
    $this->redirect('default:admin/restoration.receipt', array('id'=>$restoration->id));
  }
}
