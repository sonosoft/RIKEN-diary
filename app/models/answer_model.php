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
  protected $relations = array(
    'visit'=>array(
      'type'=>'belongsTo',
      'model'=>'Visit',
      'conditions'=>array('visit_id'=>'id')
    ),
  );

  /* ===== ===== */

  public function collectByPage($user, $visit, $page){
    /**/
    return $this->all(
      array('where'=>'[user_id] = :user_id AND [visit_id] = :visit_id AND [page] = :page'),
      array('user_id'=>$user->id, 'visit_id'=>$visit->id, 'page'=>$page)
    );
  }
}

class AnswerModel extends Model {
}
