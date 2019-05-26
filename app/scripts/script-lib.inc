<?php

/**/
define('ELN_PROJECT_ROOT', dirname(__DIR__));
define('ELNATH_ROOT', 'elnath-2.4.1');

/**/
set_include_path(ELN_PROJECT_ROOT . PATH_SEPARATOR . get_include_path());
$paths = include_once('config/paths.php');
if(is_array($paths)){
  foreach($paths as $path){
    set_include_path($path . PATH_SEPARATOR . get_include_path());
  }
}
/**/
include_once(ELNATH_ROOT . '/lib/eln.exception.php');
include_once(ELNATH_ROOT . '/lib/eln.object.php');
include_once(ELNATH_ROOT . '/lib/eln.date.php');
include_once(ELNATH_ROOT . '/lib/eln.application.php');
include_once(ELNATH_ROOT . '/lib/eln.router.php');
include_once(ELNATH_ROOT . '/lib/eln.database.php');
include_once(ELNATH_ROOT . '/lib/eln.model.php');
include_once(ELNATH_ROOT . '/lib/mod/eln.mail.php');
include_once(ELNATH_ROOT . '/lib/mod/eln.csv.php');

/**/
include_once('config/application.php');
include_once('config/database.php');
/**/
include_once('application.php');
include_once('models/model.php');

/**/
mb_language('Japanese');
mb_internal_encoding('UTF-8');

/* ===== ===== */

function getApp(){
  return Eln_Application::getInstance();
}
function getModel($name){
  $app = getApp();
  $file = 'models/' . $app->decamelize($name) . '_model.php';
  if(file_exists($app->projectFile($file)) === false){
    return null;
  }
  include_once($file);
  return Eln_ModelFactory::getInstance($name.'ModelFactory');
}
function getModule($name){
  $app = getApp();
  $file = ELNATH_ROOT . '/lib/mod/eln.' . $app->decamelize($name) . '.php';
  if(file_exists($app->projectFile($file)) === false){
    return null;
  }
  include_once($file);
  $moduleClass = 'Eln_'.$name.'Module';
  if(class_exists($moduleClass) === false){
    return null;
  }
  return new $moduleClass();
}
