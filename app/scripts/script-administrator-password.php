<?php

/**/
include_once(__DIR__.'/script-lib.inc');

/* アプリケーション */
$app = getApp();

/* モデル */
$administratorModel = getModel('Administrator');
var_dump($argv);
if(isset($argv[1])){
  echo '"'.$argv[1].'" encrypted into: '.PHP_EOL;
  echo $administratorModel->encrypt($argv[1]).PHP_EOL;
}else{
  echo 'no source string.'.PHP_EOL;
}
