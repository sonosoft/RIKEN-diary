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
      $this->app->data['diary']['from_time_h'] = intval($diary->from_time / 100);
      $this->app->data['diary']['from_time_m'] = intval($diary->from_time % 100);
      $this->app->data['diary']['to_time_h'] = intval($diary->to_time / 100);
      $this->app->data['diary']['to_time_m'] = intval($diary->to_time % 100);
    }else{
      $this->app->data['diary'] = array('id'=>null);
    }

    /**/
    return $this->viewForm();
  }
}
