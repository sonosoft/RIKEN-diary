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

  public function findById($id, $all=false){
    if($all){
      return $this->one(array('where'=>'[id] = :id'), array('id'=>$id));
    }
    return $this->one(array('where'=>'[id] = :id AND [status] = :enabled'), array('id'=>$id, 'enabled'=>STATUS_ENABLED));
  }

  public function findByCode($code){
    return $this->one(array('where'=>'[code] = :code AND [status] = :enabled'), array('code'=>$code, 'enabled'=>STATUS_ENABLED));
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
      if(($data = json_decode($this->schedule, true)) !== false){
	switch($data['flag']){
	case MAIL_BEFORE:
	  return '計測前'.$data['before'].'日';
	case MAIL_AFTER:
	  return '計測後'.$data['after'].'日';
	case MAIL_DURING:
	  return '計測期間中';
	case MAIL_DATE:
	  return $data['date'];
	}
      }
      return '';
    }
    return null;
  }
}
