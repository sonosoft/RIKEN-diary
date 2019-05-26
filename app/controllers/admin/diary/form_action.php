<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/diary/form_action.php
 */


class AdminDiaryFormAction extends AdminDiaryController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Diary');

    /* モデル */
    if($this->app->route['id'] !== null){
      if(($diary = $this->DiaryModel->findById($this->app->route['id'])) === null){
	$this->redirect('default:admin/diary.list');
      }
      $this->app->data['diary'] = $diary->getAttributes();
    }else{
      $this->app->data['diary'] = array('id'=>null);
    }

    /**/
    return $this->viewForm();
  }
}
