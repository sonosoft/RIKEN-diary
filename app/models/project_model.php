<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * project_model.php
 */


class ProjectModelFactory extends ModelFactory {
  /*
   * プロパティ
   */
  protected $model = 'Project';
  protected $table = 'project';

  /* ===== ===== */

  public function findById($id){
    return $this->one(array('where'=>'[id] = :id AND [status] = :enabled'), array('id'=>$id, 'enabled'=>STATUS_ENABLED));
  }
  
  public function generateRandom(){
    $base = array_merge(range(2, 9), range('a', 'k'), range('m', 'z'), range('A', 'H'), range('J', 'N'), range('P', 'Z'));
    while(true){
      $random = '';
      while(strlen($random) < 10){
	$random .= $base[array_rand($base)];
      }
      if($this->one(array('where'=>'[randomString] = :random'), array('random'=>$random)) === null){
	return $random;
      }
    }
  }
}

class ProjectModel extends Model {
}
