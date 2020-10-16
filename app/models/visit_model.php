<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * visit_model.php
 */


class VisitModelFactory extends ModelFactory {
  /*
   * プロパティ
   */
  protected $model = 'Visit';
  protected $table = 'visit';
  
  /* ===== ===== */

  public function findById($id){
    return $this->one(array('where'=>'[id] = :id'), array('id'=>$id));
  }
}

class VisitModel extends Model {
  public function __isset($name){
    if(strcmp($name, 'timing_tos') == 0){
      return true;
    }
    return false;
  }
  public function __get($name){
    if(strcmp($name, 'timing_tos') == 0){
      switch($this->timing){
      case TIMING_GETUP:
	return '起床時';
      case TIMING_AM:
	return '午前';
      case TIMING_PM:
	return '午後';
      case TIMING_GOTOBED:
	return '就寝時';
      }
    }
    return null;
  }
}
