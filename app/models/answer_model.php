<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * answer_model.php
 */


class AnswerModelFactory extends ModelFactory {
  /*
   * プロパティ
   */
  protected $model = 'Answer';
  protected $table = 'answer';

  /* ===== ===== */

  public function collectByPage($user, $visit, $page){
    /**/
    return $this->all(
      array('where'=>'[user_id] = :user_id AND [visit_id] = :visit_id AND [page] = :page'),
      array('user_id'=>$user->id, 'visit_id'=>$visit->id, 'page'=>$page)
    );
  }

  public function findLatestAnswer($user, $visit, $name){
    /**/
    $where = array(
      '[user_id] = :user_id',
      '[visit_id] != :visit_id',
      '[name] = :name',
      '[value] IS NOT NULL',
      '[answered_at] < :answered_at',
    );
    return $this->one(
      array('where'=>implode(' AND ', $where)),
      array('user_id'=>$user->id, 'visit_id'=>$visit->id, 'name'=>$name, 'answered_at'=>$visit->stareted_at)
    );
  }
}

class AnswerModel extends Model {
}
