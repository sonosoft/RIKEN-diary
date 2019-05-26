<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * mail_model.php
 */


class MailModelFactory extends ModelFactory {
  /*
   * プロパティ
   */
  protected $model = 'Mail';
  protected $table = 'mail';

  /* ===== ===== */

  public function findById($id){
    return $this->one(array('where'=>'[id] = :id AND [status] = :enabled'), array('id'=>$id, 'enabled'=>STATUS_ENABLED));
  }

  public function replace($src, $user, $dialy){
    $dst = str_replace('【氏名】', $user->family_name.' '.$user->first_name, $src);
    if($measurement !== null){
      $dst = str_replace('【計測開始日】', $measurement->measurementDate->format('%Y年%m月%d日（%a）%H時'), $dst);
    }
    if($measurement !== null){
      $dst = str_replace('【計測終了日】', $measurement->measurementDate->format('%Y年%m月%d日（%a）%H時'), $dst);
    }
    $dst = str_replace('【乱数】', $user->token, $dst);
    return $dst;
  }
}

class MailModel extends Model {
  public function __isset($name){
    if(strcmp($name, 'schedule_tos') == 0){
      return true;
    }
    return false;
  }
  public function __get($name){
    if(strcmp($name, 'schedule_tos') == 0){
      return '';
    }
    return null;
  }
}
