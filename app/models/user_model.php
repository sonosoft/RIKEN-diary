<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * user_model.php
 */


class UserModelFactory extends ModelFactory {
  /*
   * プロパティ
   */
  protected $model = 'User';
  protected $table = 'users';
  protected $relations = array(
    'measurement'=>array(
      'type'=>'belongsTo',
      'model'=>'Measurement',
      'conditions'=>array('belongMeasurementDateID'=>'id')
    ),
  );

  /* ===== ===== */

  public function findById($id){
    return $this->one(array('where'=>'[id] = :id AND [status] = :enabled'), array('id'=>$id, 'enabled'=>STATUS_ENABLED));
  }
  
  public function findCancellable($random){
    return $this->one(
      array('where'=>'[randomString] = :random AND [deleted_at] IS NULL'),
      array('random'=>$random)
    );
  }

  public function getCodes($measurementId){
    $codes = array();
    $users = $this->all(
      array('where'=>'[belongMeasurementDateID] = :id AND [deleted_at] IS NULL'),
      array('id'=>$measurementId)
    );
    foreach($users as $user){
      $codes[] = $user->userID;
    }
    return $codes;
  }

  public function generateCode($date, $index){
    return sprintf('M%s%05d', $date->format('%y%m%d'), $index);
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

class UserModel extends Model {
  private static $exNames = array(
    'userIDa',
    'sex_tos',
    'progress',
    'questionnaire_tos',
    'contacting_tos',
  );
  public function __isset($name){
    return in_array($name, self::$exNames);
  }
  public function __get($name){
    if(strcmp($name, 'userIDa') == 0){
      return 'M'.substr($this->userID, -5);
    }else if(strcmp($name, 'sex_tos') == 0){
      if($this->isMan){
	return '男性';
      }
      return '女性';
    }else if(strcmp($name, 'progress') == 0){
      if($this->questionnaireDate){
	return '質問紙';
      }else if($this->acceptanceDate){
	return '受付確認';
      }
      return '';
    }else if(strcmp($name, 'questionnaire_tos') == 0){
      if($this->isSentQuestionnaire){
	return '回答済';
      }
      return '未回答';
    }else if(strcmp($name, 'contacting_tos') == 0){
      if($this->isContactOk){
	return 'はい';
      }
      return 'いいえ';
    }
    return null;
  }
}
