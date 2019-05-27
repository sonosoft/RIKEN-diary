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
}

class ProjectModel extends Model {
}
