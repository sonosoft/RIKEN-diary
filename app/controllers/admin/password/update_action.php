<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/password/update_action.php
 */


class AdminPasswordUpdateAction extends AdminController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Administrator');
    $this->useValidator('AdministratorPassword');

    /* トランザクション */
    $this->db->begin();

    /**/
    try{
      /* 検証 */
      $data = $this->app->readRequest('password', array());
      $data['curr'] = $this->session->administrator->password;
      $data = $this->AdministratorPasswordValidator->validate($data);

      /* 更新 */
      $this->session->administrator->password = $this->AdministratorModel->encrypt($data['new1']);
      $this->session->administrator->updated_at = $this->app->data['_now_'];
      $this->session->administrator->save();
      
      /* コミット */
      $this->db->commit();
    }catch(Exception $e){
      /* ロールバック */
      $this->db->rollback();
    }
    
    /**/
    $this->redirect('default:admin/password.receipt');
  }
}
