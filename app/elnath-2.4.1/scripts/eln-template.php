<?php

/**/
if($argc < 2){
  echo PHP_EOL;
  echo 'ERROR!!' . PHP_EOL;
  echo '[usage]' . PHP_EOL;
  echo '  eln-template.php [--nolayout] template_name ...' . PHP_EOL;
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
$layoutFlag = true;
$templates = array();
for($cnt = 1; $cnt < $argc; ++ $cnt){
  if(strcmp($argv[$cnt], '--nolayout') == 0){
    $layoutFlag = false;
  }else{
    $templates[] = $argv[$cnt];
  }
}
if(empty($templates)){
  echo PHP_EOL;
  echo 'ERROR!!' . PHP_EOL;
  echo '[usage]' . PHP_EOL;
  echo '  eln-template.php [--nolayout] template_name ...' . PHP_EOL;
  echo PHP_EOL;
  exit;
}

/**/
try{
  /* テンプレート */
  $template1 = implode(DIRECTORY_SEPARATOR, array(ELN_SCRIPT_ROOT, 'templates', 'view-html-super.eln'));
  $template2 = implode(DIRECTORY_SEPARATOR, array(ELN_SCRIPT_ROOT, 'templates', 'view-html-sub.eln'));
  $template3 = implode(DIRECTORY_SEPARATOR, array(ELN_SCRIPT_ROOT, 'templates', 'view-html-solo.eln'));

  /**/
  foreach($templates as $template){
    /* パス */
    $segments = explode('/', trim($template, '/'));
    $name = array_pop($segments);
    if(empty($name)){
      throw new Exception(sprintf('The template name "%s" is invalid.', $template));
      return null;
    }
    $path = array();
    foreach($segments as $segment){
      if(strlen($segment) > 0){
	if(!preg_match('/^[a-zA-Z][_0-9a-zA-Z]*$/', $segment)){
	  throw new Exception(sprintf('"%s" is illegal name for template.', $template));
	}
	$path[] = $segment;
      }
    }
    elnCreateDirectory('templates', ELN_PROJECT_ROOT, $path);

    /**/
    if($layoutFlag){
      $pa = array_merge(array(ELN_PROJECT_ROOT, 'templates'), $path, array($name.'_layout.html'));
      $file = implode(DIRECTORY_SEPARATOR, $pa);
      if(file_exists($file)){
	echo sprintf('- template HTML "%s" already exists.', $file) . PHP_EOL;
      }else{
	$data = file_get_contents($template1);
	file_put_contents($file, $data);
	echo sprintf('+ create template HTML file "%s".', $file) . PHP_EOL;
      }
    }
    $pa = array_merge(array(ELN_PROJECT_ROOT, 'templates'), $path, array($name.'.html'));
    $file = implode(DIRECTORY_SEPARATOR, $pa);
    if(file_exists($file)){
      echo sprintf('- template HTML "%s" already exists.', $file) . PHP_EOL;
    }else{
      if($layoutFlag){
	$data = file_get_contents($template2);
      }else{
	$data = file_get_contents($template3);
      }
      file_put_contents($file, $data);
      echo sprintf('+ create template HTML file "%s".', $file) . PHP_EOL;
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
echo 'setting up template is successfully finished.' . PHP_EOL;
echo PHP_EOL;
exit;
