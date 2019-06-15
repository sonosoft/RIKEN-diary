<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * diary_model.php
 */


class DiaryModelFactory extends ModelFactory {
  /*
   * プロパティ
   */
  protected $model = 'Diary';
  protected $table = 'diary';

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
}

class DiaryModel extends Model {
  public function __isset($name){
    if(strcmp($name, 'path') == 0){
      return true;
    }else if(strcmp($name, 'schedule_tos') == 0){
      return true;
    }else if(strcmp($name, 'tos') == 0){
      return true;
    }
    return false;
  }
  public function __get($name){
    if(strcmp($name, 'path') == 0){
      $hex = sprintf('%08x', $this->id);
      $path = 'var/data';
      foreach(array(0, 2, 4, 6) as $i){
	$path = sprintf('%s/%02x', $path, substr($hex, $i, 2));
      }
      $path .= '.xml';
      return $this->app->projectFile($path);
    }else if(strcmp($name, 'schedule_tos') == 0){
      return sprintf(
	'%02d:%02d〜%02d:%02d',
	intval($this->from_time / 100), intval($this->from_time % 100),
	intval($this->to_time / 100), intval($this->to_time % 100)
      );
    }else if(strcmp($name, 'tos') == 0){
      return 'DY'.$this->code.':'.$this->title;
    }
    return null;
  }

  /* ===== ===== */

  public function isActive($datetime){
    if($this->from_time == 0 && $this->to_time == 0){
      return true;
    }
    $time = $datetime->hour * 100 + $datetime->minute;
    if($this->from_time <= $time && $this->to_time >= $time){
      return true;
    }
    return false;
  }
}
