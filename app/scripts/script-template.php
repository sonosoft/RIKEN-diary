<?php

/**/
include_once(__DIR__.'/script-lib.inc');

/* アプリケーション */
$app = getApp();

/**/
try{
  /* 現在日時 */
  $now = Eln_Date::now();

  /* データベース接続 */
  $db = Eln_Database::setCurrentDatabase(CONNECTION_NAME);
  $db->open();
  $db->begin();

  /* モデル */
  $testModel = getModel('Test');

  /*
   * -----
   * -----
   * -----
   * -----
   * -----
   */

  /* コミット */
  $db->commit();
}catch(Exception $e){
  /* ロールバック */
  $db->rollback();

  /* エラー */
  echo '***** ERROR ***** ['.date('Y/m/d H:i:s').'] *****'.PHP_EOL;
  echo $e->getMessage().PHP_EOL;
  echo '----- ----- ----- ----- -----'.PHP_EOL;
  echo debug_print_backtrace();
  echo '----- ----- ----- ----- -----'.PHP_EOL;
  echo PHP_EOL;
}
