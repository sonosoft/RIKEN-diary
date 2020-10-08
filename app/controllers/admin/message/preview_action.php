<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/message/preview_action.php
 */


class AdminMessagePreviewAction extends AdminMessageController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('User', 'Message');
    
    /**/
    $message = $this->MessageModel->one(
      array('where'=>'[id] = :id AND status = :enabled'),
      array('id'=>$this->app->route['id'], 'enabled'=>STATUS_ENABLED)
    );
    if($message === null){
      $this->redirect('default:admin/error.invalid_access');
    }
    if(($ids = json_decode($message->destinations, true)) === null){
      $this->redirect('default:admin/error.invalid_access');
    }
    $users = $this->UserModel->all(
      array('where'=>'[id] IN :ids'),
      array('ids'=>$ids)
    );
    /**/
    $this->app->data['message'] = $message;
    $this->app->data['users'] = $users;

    /**/
    return 'admin/message/preview';
  }
}
