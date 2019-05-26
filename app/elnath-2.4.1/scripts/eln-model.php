<?php

/**/
if($argc < 2){
  echo PHP_EOL;
  echo 'ERROR!!' . PHP_EOL;
  echo '[usage]' . PHP_EOL;
  echo '  eln-model.php [--novalidator] [table_name:remote_name ...]' . PHP_EOL;
  echo PHP_EOL;
  exit;
}
/**/
echo PHP_EOL;
echo '[Elnath PHP Web Application Framework]' . PHP_EOL;
echo 'setting up model...' . PHP_EOL;
echo PHP_EOL;

/**/
include_once(__DIR__ . DIRECTORY_SEPARATOR . '_elnath.php');
include_once(__DIR__ . DIRECTORY_SEPARATOR . '_directory.php');

/* パラメータ */
$validatorFlag = true;
$tables = array();
for($cnt = 1; $cnt < $argc; ++ $cnt){
  if(strcmp($argv[$cnt], '--novalidator') == 0){
    $validatorFlag = false;
  }else if(preg_match('/^[a-zA-Z][_0-9a-zA-Z]*(?::[a-zA-Z][_0-9a-zA-Z]*)?$/', $argv[$cnt])){
    $tables[] = $argv[$cnt];
  }
}
if(empty($tables)){
  echo PHP_EOL;
  echo 'ERROR!!' . PHP_EOL;
  echo '[usage]' . PHP_EOL;
  echo '  eln-model.php [--novalidator] [table_name:remote_name ...]' . PHP_EOL;
  echo PHP_EOL;
  exit;
}

/**/
try{
  /* テンプレート */
  $templateM = implode(DIRECTORY_SEPARATOR, array(ELN_SCRIPT_ROOT, 'templates', 'model.eln'));
  $templateS = implode(DIRECTORY_SEPARATOR, array(ELN_SCRIPT_ROOT, 'templates', 'validator-search.eln'));
  $templateF = implode(DIRECTORY_SEPARATOR, array(ELN_SCRIPT_ROOT, 'templates', 'validator-form.eln'));

  /* モデル */
  foreach($tables as $name){
    if(preg_match('/^(.+)(?::(.+))$/', $name, $matches)){
      $tableName = $matches[1];
      $remoteName = $matches[2];
    }else{
      $tableName = $remoteName = $name;
    }
    $modelClass = eln_camelize($tableName);
    //
    $file = implode(DIRECTORY_SEPARATOR, array(ELN_PROJECT_ROOT, 'models', $tableName . '_model.php'));
    if(file_exists($file)){
      echo sprintf('- model file "%s" already exists.', $file) . PHP_EOL;
    }else{
      $data = file_get_contents($templateM);
      $data = str_replace('${_remote_name_}', $remoteName, $data);
      $data = str_replace('${_table_name_}', $tableName, $data);
      $data = str_replace('${_model_class_}', $modelClass, $data);
      file_put_contents($file, $data);
      echo sprintf('+ create model file "%s".', $file) . PHP_EOL;
    }
    //
    if($validatorFlag){
      $file  = implode(DIRECTORY_SEPARATOR, array(ELN_PROJECT_ROOT, 'validators'));
      $file .= DIRECTORY_SEPARATOR . $tableName . '_search_validator.php';
      if(file_exists($file)){
	echo sprintf('- validator file "%s" already exists.', $file) . PHP_EOL;
      }else{
	$data = file_get_contents($templateS);
	$data = str_replace('${_table_name_}', $tableName, $data);
	$data = str_replace('${_model_class_}', $modelClass, $data);
	file_put_contents($file, $data);
	echo sprintf('+ create validator file "%s".', $file) . PHP_EOL;
      }
    }
    //
    if($validatorFlag){
      $file  = implode(DIRECTORY_SEPARATOR, array(ELN_PROJECT_ROOT, 'validators'));
      $file .= DIRECTORY_SEPARATOR . $tableName . '_form_validator.php';
      if(file_exists($file)){
	echo sprintf('- validator file "%s" already exists.', $file) . PHP_EOL;
      }else{
	$data = file_get_contents($templateF);
	$data = str_replace('${_table_name_}', $tableName, $data);
	$data = str_replace('${_model_class_}', $modelClass, $data);
	file_put_contents($file, $data);
	echo sprintf('+ create validator file "%s".', $file) . PHP_EOL;
      }
    }
  }
}catch(Exception $e){
  echo PHP_EOL;
  echo 'ERROR!!' . PHP_EOL;
  echo $e->getMessage() . PHP_EOL;
  echo PHP_EOL;
  echo '...failed to set up model.';
  echo PHP_EOL;
  exit;
}

/* 終了 */
echo PHP_EOL;
echo 'setting up model is successfully finished.' . PHP_EOL;
echo PHP_EOL;
exit;
