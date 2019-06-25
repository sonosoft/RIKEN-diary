<?php

/**/
include_once(__DIR__.'/script-lib.inc');

/* アプリケーション */
$app = getApp();

/* データベース接続 */
$db = Eln_Database::setCurrentDatabase('database');
$db->open();

/* 日誌 */
$diaryModel = getModel('Diary');
if(($diary = $diaryModel->one(array('where'=>'[code] = :code'), array('code'=>'116'))) !== null){
  $datetime = new Eln_Date();
  foreach(range(0, 23) as $hour){
    $datetime->hour = $hour;
    $datetime->minute = 0;
    if($diary->isActive($datetime)){
      echo $datetime->format('%H:%M').' is OK!'.PHP_EOL;
    }else{
      echo $datetime->format('%H:%M').' is NG!'.PHP_EOL;
    }
  }
}
