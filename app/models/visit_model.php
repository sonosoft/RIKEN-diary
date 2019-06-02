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
}
