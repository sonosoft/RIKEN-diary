<?php

/**/
if($argc < 2){
  echo PHP_EOL;
  echo 'ERROR!!' . PHP_EOL;
  echo '[usage]' . PHP_EOL;
  echo '  eln-controller.php [--nosuper] controller_name [action_name ...]' . PHP_EOL;
  echo PHP_EOL;
  exit;
}
/**/
echo PHP_EOL;
echo '[Elnath PHP Web Application Framework]' . PHP_EOL;
echo 'setting up controller...' . PHP_EOL;
echo PHP_EOL;

/**/
include_once(__DIR__ . DIRECTORY_SEPARATOR . '_elnath.php');
include_once(__DIR__ . DIRECTORY_SEPARATOR . '_directory.php');

/**/
function _pluralize($name){
  if(strcmp(substr($name, -3), 'man') == 0){
    return substr($name, 0, -3).'men';
  }else if(preg_match('/^(.+[^aeiou])y$/', $name, $matches)){
    return $matches[1].'ies';
  }else if(preg_match('/^(.+)fe?$/', $name, $matches)){
    return $matches[1].'ves';
  }else if(preg_match('/(?:sh|ch|s|x)$/', $name)){
    return $name.'es';
  }
  return $name.'s';
}

/* パラメータ */
$superFlag = true;
$controller = null;
$actions = array();
for($cnt = 1; $cnt < $argc; ++ $cnt){
  if(strcmp($argv[$cnt], '--nosuper') == 0){
    $superFlag = false;
  }else if(strcmp(substr($argv[$cnt], 0, 2), '--') == 0){
    $controller = null;
    break;
  }else if($controller === null){
    $controller = $argv[$cnt];
  }else if(preg_match('/^[a-zA-Z][_0-9a-zA-Z]*$/', $argv[$cnt])){
    $actions[] = $argv[$cnt];
  }
}
if($controller === null){
  echo PHP_EOL;
  echo 'ERROR!!' . PHP_EOL;
  echo '[usage]' . PHP_EOL;
  echo '  eln-controller.php [--nosuper] controller_name [action_name ...]' . PHP_EOL;
  echo PHP_EOL;
  exit;
}

/**/
try{
  /* パス */
  $path = array();
  foreach(explode('/', trim($controller, '/')) as $segment){
    if(strlen($segment) > 0){
      if(!preg_match('/^[a-zA-Z][_0-9a-zA-Z]*$/', $segment)){
	throw new Exception(sprintf('"%s" is illegal name for controller.', $segment));
      }
      $path[] = $segment;
    }
  }
  if(empty($path)){
    throw new Exception(sprintf('The controller name "%s" is invalid.', $controller));
    return null;
  }
  elnCreateDirectory('controllers', ELN_PROJECT_ROOT, $path);

  /* モデル */
  $modelName = end($path);
  $modelClass = eln_camelize($modelName);

  /* テンプレート */
  $template = implode(DIRECTORY_SEPARATOR, array(ELN_SCRIPT_ROOT, 'templates', 'controller.eln'));

  /* コントローラ */
  $dirname = '';
  $current = '';
  $parent = '';
  while(true){
    if(empty($path)){
      break;
    }
    $segment = array_shift($path);
    if(empty($dirname) === false){
      $dirname .= '/';
    }
    $dirname .= $segment;
    $current .= eln_camelize($segment);
    /**/
    $file = implode(DIRECTORY_SEPARATOR, array(ELN_PROJECT_ROOT, 'controllers', $dirname));
    $file = rtrim($file, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'controller.php';
    if($superFlag){
      if(file_exists($file)){
	echo sprintf('- controller file "%s" already exists.', $file) . PHP_EOL;
      }else{
	$data = file_get_contents($template);
	$data = str_replace('${_controller_directory_}', $dirname . '/', $data);
	$data = str_replace('${_controller_name_}', $dirname, $data);
	$data = str_replace('${_controller_class_}', $current, $data);
	$data = str_replace('${_parent_controller_class_}', $parent, $data);
	$data = str_replace('${_model_name_}', $modelName, $data);
	$data = str_replace('${_model_pname_}', _pluralize($modelName), $data);
	$data = str_replace('${_model_class_}', $modelClass, $data);
	file_put_contents($file, $data);
	echo sprintf('+ create controller file "%s".', $file) . PHP_EOL;
      }
      $parent = $current;
    }
  }

  /* アクション */
  $controller = $current;
  foreach($actions as $actionName){
    $file = implode(DIRECTORY_SEPARATOR, array(ELN_PROJECT_ROOT, 'controllers', $dirname));
    $file = rtrim($file, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $actionName . '_action.php';
    if(file_exists($file)){
      echo sprintf('- action file "%s" already exists.', $file) . PHP_EOL;
    }else{
      $actionClass = $controller . eln_camelize($actionName);
      //
      $template = sprintf('action-%s.eln', $actionName);
      $template = implode(DIRECTORY_SEPARATOR, array(ELN_SCRIPT_ROOT, 'templates', $template));
      if(file_exists($template) === false){
	$template = implode(DIRECTORY_SEPARATOR, array(ELN_SCRIPT_ROOT, 'templates', 'action.eln'));
      }
      $data = file_get_contents($template);
      $data = str_replace('${_action_directory_}', $dirname, $data);
      $data = str_replace('${_action_name_}', $actionName, $data);
      $data = str_replace('${_action_class_}', $actionClass, $data);
      $data = str_replace('${_controller_class_}', $controller, $data);
      $data = str_replace('${_model_name_}', $modelName, $data);
      $data = str_replace('${_model_class_}', $modelClass, $data);
      file_put_contents($file, $data);
      echo sprintf('+ create action file "%s".', $file) . PHP_EOL;
    }
  }
}catch(Exception $e){
  echo PHP_EOL;
  echo 'ERROR!!' . PHP_EOL;
  echo $e->getMessage() . PHP_EOL;
  echo PHP_EOL;
  echo '...failed to set up controller.';
  echo PHP_EOL;
  exit;
}

/* 終了 */
echo PHP_EOL;
echo 'setting up controller is successfully finished.' . PHP_EOL;
echo PHP_EOL;
exit;
