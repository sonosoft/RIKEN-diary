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

  public function collectChoices($default=''){
    $choices = array();
    if(empty($default) === false){
      $choices[] = array('value'=>'', 'label'=>$default);
    }
    foreach($this->all(array('where'=>'[status] = :enabled', 'order'=>'[code] ASC'), array('enabled'=>STATUS_ENABLED)) as $record){
      $choices[] = array('value'=>$record->id, 'label'=>$record->tos);
    }
    return $choices;
  }
  
  public function replace($src, $project, $user){
    $dst = $src;
    $dst = str_replace('【氏名】', $user->family_name.' '.$user->first_name, $dst);
    if($project !== null){
      $dst = str_replace('【計測開始日】', $project->from_date->format('%Y年%m月%d日（%a）'), $dst);
      $dst = str_replace('【計測終了日】', $project->to_date->format('%Y年%m月%d日（%a）'), $dst);
      $dst = str_replace('【アドレス】', DEFAULT_BASE_URL.$project->token.$user->token, $dst);
      $dst = str_replace('【乱数】', $project->token.$user->token, $dst);
    }
    return $dst;
  }
}

class MailModel extends Model {
  public function __isset($name){
    if(strcmp($name, 'schedule_tos') == 0){
      return true;
    }else if(strcmp($name, 'tos') == 0){
      return true;
    }
    return false;
  }
  public function __get($name){
    if(strcmp($name, 'schedule_tos') == 0){
      $info = '';
      if(($data = json_decode($this->schedule, true)) !== false){
	switch($data['flag']){
	case MAIL_BEFORE:
	  $info .= '計測'.$data['before'].'日前';
	  break;
	case MAIL_AFTER:
	  $info .= '計測'.$data['after'].'日後';
	  break;
	case MAIL_DURING:
	  $info .= '計測期間中';
	case MAIL_DATE:
	  $info .= $data['date'];
	}
	$times = array();
	foreach($data['times'] as $time){
	  $times[] = sprintf('%02d:%02d', intval($time / 100), intval($time % 100));
	}
	$info .= ' ['.implode('/', $times).']';
      }
      return $info;
    }else if(strcmp($name, 'tos') == 0){
      return 'ML'.$this->code.':'.$this->title;
    }
    return null;
  }
}
