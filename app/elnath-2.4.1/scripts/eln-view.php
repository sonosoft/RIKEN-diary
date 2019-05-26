<?php

/**/
if($argc < 2){
  echo PHP_EOL;
  echo 'ERROR!!' . PHP_EOL;
  echo '[usage]' . PHP_EOL;
  echo '  eln-view.php [--nosuper] view_directory [view_name ...]' . PHP_EOL;
  echo PHP_EOL;
  exit;
}
/**/
echo PHP_EOL;
echo '[Elnath PHP Web Application Framework]' . PHP_EOL;
echo 'setting up view...' . PHP_EOL;
echo PHP_EOL;

/**/
include_once(__DIR__ . DIRECTORY_SEPARATOR . '_elnath.php');
include_once(__DIR__ . DIRECTORY_SEPARATOR . '_directory.php');

/* パラメータ */
$superFlag = true;
$viewRoot = null;
$views = array();
for($cnt = 1; $cnt < $argc; ++ $cnt){
  if(strcmp($argv[$cnt], '--nosuper') == 0){
    $superFlag = false;
  }else if(strcmp(substr($argv[$cnt], 0, 2), '--') == 0){
    $viewRoot = null;
    break;
  }else if($viewRoot === null){
    $viewRoot = $argv[$cnt];
  }else if(preg_match('/^[a-zA-Z][_0-9a-zA-Z]*$/', $argv[$cnt])){
    $views[] = $argv[$cnt];
  }
}
if($viewRoot === null){
  echo PHP_EOL;
  echo 'ERROR!!' . PHP_EOL;
  echo '[usage]' . PHP_EOL;
  echo '  eln-view.php [--nosuper] view_directory [view_name ...]' . PHP_EOL;
  echo PHP_EOL;
  exit;
}

/**/
try{
  /* パス */
  $path = array();
  foreach(explode('/', trim($viewRoot, '/')) as $segment){
    if(strlen($segment) > 0){
      if(!preg_match('/^[a-zA-Z][_0-9a-zA-Z]*$/', $segment)){
	throw new Exception(sprintf('"%s" is illegal name for view.', $segment));
      }
      $path[] = $segment;
    }
  }
  if(empty($path)){
    throw new Exception(sprintf('The view name "%s" is invalid.', $viewRoot));
    return null;
  }
  elnCreateDirectory('views', ELN_PROJECT_ROOT, $path);

  /* テンプレート */
  $template = implode(DIRECTORY_SEPARATOR, array(ELN_SCRIPT_ROOT, 'templates', 'view-php.eln'));

  /* ビュー */
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
    $dir = implode(DIRECTORY_SEPARATOR, array(ELN_PROJECT_ROOT, 'views', $dirname));
    if($superFlag){
      $templ = rtrim($dirname, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'layout.html';
      $file = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'view.php';
      if(file_exists($file)){
	echo sprintf('- view PHP file "%s" already exists.', $file) . PHP_EOL;
      }else{
	$data = file_get_contents($template);
	$data = str_replace('${_view_name_}', $dirname . '/', $data);
	$data = str_replace('${_view_class_}', $current, $data);
	$data = str_replace('${_parent_view_class_}', $parent, $data);
	$data = str_replace('${_template_name_}', $templ, $data);
	file_put_contents($file, $data);
	echo sprintf('+ create view PHP file "%s".', $file) . PHP_EOL;
      }
      $parent = $current;
    }
  }
  /**/
  $view = $current;
  foreach($views as $viewName){
    $dir = implode(DIRECTORY_SEPARATOR, array(ELN_PROJECT_ROOT, 'views', $dirname));
    $templ = rtrim($dirname, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $viewName . '.html';
    $file = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $viewName . '_view.php';
    if(file_exists($file)){
      echo sprintf('- view PHP file "%s" already exists.', $file) . PHP_EOL;
    }else{
      $viewClass = $view . eln_camelize($viewName);
      //
      $data = file_get_contents($template);
      $data = str_replace('${_view_name_}', $dirname . '/' . $viewName . '_', $data);
      $data = str_replace('${_view_class_}', $viewClass, $data);
      $data = str_replace('${_parent_view_class_}', $view, $data);
      $data = str_replace('${_template_name_}', $templ, $data);
      file_put_contents($file, $data);
      echo sprintf('+ create view PHP file "%s".', $file) . PHP_EOL;
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
echo 'setting up view is successfully finished.' . PHP_EOL;
echo PHP_EOL;
exit;
