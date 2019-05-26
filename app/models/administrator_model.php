<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * administrator_model.php
 */


class AdministratorModelFactory extends ModelFactory {
  /*
   * プロパティ
   */
  protected $model = 'Administrator';
  protected $table = 'administrator';

  /* ===== ===== */

  public function findById($id){
    return $this->one(
      array('where'=>'[id] = :id'),
      array('id'=>$id)
    );
  }
  
  public function authenticate($password){
    return $this->one(
      array('where'=>'[password] = :password'),
      array('password'=>$this->encrypt($password))
    );
  }

  public function encrypt($src){
    return sha1(md5($src).sha1($src).';');
  }
}

class AdministratorModel extends Model {
}
