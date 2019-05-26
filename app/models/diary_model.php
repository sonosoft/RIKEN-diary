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

  public function findById($id){
    return $this->one(array('where'=>'[id] = :id AND [status] = :enabled'), array('id'=>$id, 'enabled'=>STATUS_ENABLED));
  }
}

class DiaryModel extends Model {
  public function __isset($name){
    if(strcmp($name, 'path') == 0){
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
    }
    return null;
  }
}
