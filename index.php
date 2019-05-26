<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * index.php
 */


/*
 * 定数
 */

/* アプリケーションルート */
define('ELN_PUBLIC_ROOT', __DIR__);
define('ELN_PROJECT_ROOT', ELN_PUBLIC_ROOT . DIRECTORY_SEPARATOR . 'app');

/* URI */
if(array_key_exists('SSL_PROTOCOL', $_SERVER) || array_key_exists('HTTPS', $_SERVER)){
  define('ELN_WWW_PROTOCOL', 'https://');
}else{
  define('ELN_WWW_PROTOCOL', 'http://');
}
define('ELN_WWW_HOST', $_SERVER['HTTP_HOST']);
define('ELN_WWW_ROOT', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/');

/* ライブラリルート */
define('ELNATH_ROOT', 'elnath-2.4.1');


/* ===== ===== */

/*
 * 基本設定
 */

/* インクルードパス */
set_include_path(ELN_PROJECT_ROOT . PATH_SEPARATOR . get_include_path());
$elnPaths = include_once('config/paths.php');
if(is_array($elnPaths)){
  foreach($elnPaths as $path){
    set_include_path($path . PATH_SEPARATOR . get_include_path());
  }
}

/* 基本ライブラリの読み込み */
include_once(ELNATH_ROOT . '/lib/eln.exception.php');
include_once(ELNATH_ROOT . '/lib/eln.object.php');
include_once(ELNATH_ROOT . '/lib/eln.date.php');
include_once(ELNATH_ROOT . '/lib/eln.application.php');
include_once(ELNATH_ROOT . '/lib/eln.router.php');
include_once(ELNATH_ROOT . '/lib/eln.database.php');
include_once(ELNATH_ROOT . '/lib/eln.model.php');
include_once(ELNATH_ROOT . '/lib/eln.validator.php');
include_once(ELNATH_ROOT . '/lib/eln.paginator.php');


/* ===== ===== */

/*
 * フレームワーク
 */
try{
  /* 設定ファイルの読み込み */
  include_once('config/application.php');
  include_once('config/route.php');
  include_once('config/database.php');

  /* シングルトン */
  include_once('application.php');
  $elnApplication = Eln_Application::getInstance();
  $elnRouter = Eln_Router::getInstance();

  /* URLルート解析 */
  if(($elnApplication->route = $elnRouter->parse()) === false){
    throw new Eln_404();
  }

  /* モデル/バリデータ */
  if(file_exists($elnApplication->projectFile('models/model.php'))){
    include_once('models/model.php');
  }
  if(file_exists($elnApplication->projectFile('validators/validator.php'))){
    include_once('validators/validator.php');
  }

  /* アクション */
  $elnRoute = $elnApplication->route['controller'] . '/' . $elnApplication->route['action'];
  /**/
  include_once(ELNATH_ROOT . '/lib/eln.controller.php');
  $elnDirectory = 'controllers';
  $elnController = $elnDirectory . '/controller.php';
  $elnClassnameC = '';
  if(file_exists($elnApplication->projectFile($elnController))){
    include_once($elnController);
  }
  foreach(explode('/', $elnApplication->route['controller']) as $segment){
    $elnDirectory .= '/' . $segment;
    $elnController = $elnDirectory . '/controller.php';
    $elnClassnameC .= $elnApplication->camelize($segment);
    if(file_exists($elnApplication->projectFile($elnController))){
      include_once($elnController);
    }
  }
  $elnClassnameA = $elnClassnameC . $elnApplication->camelize($elnApplication->route['action']) . 'Action';
  $elnClassnameC = $elnClassnameC . 'Controller';
  /**/
  $elnFilename = 'controllers/' . $elnRoute . '_action.php';
  if(file_exists($elnApplication->projectFile($elnFilename))){
    include_once($elnFilename);
  }

  /**/
  try{
    /* アクション実行 */
    $elnOutput = null;
    if(class_exists($elnClassnameA)){
      $elnAction = new $elnClassnameA();
      $elnOutput = $elnAction->execute();
    }else if(class_exists($elnClassnameC)){
      $elnAction = new $elnClassnameC();
      if(method_exists($elnAction, $elnApplication->route['action'] . '_action') === false){
	throw new Eln_404();
      }
      $elnOutput = $elnAction->execute($elnApplication->route['action']);
    }else{
      throw new Eln_404();
    }
    if($elnOutput === null){
      $elnOutput = $elnRoute;
    }

    /* ページ出力 */
    $elnFilename = 'views/' . $elnOutput . '_view.php';
    $elnClassname = '';
    foreach(explode('/', $elnOutput) as $segment){
      $elnClassname .= $elnApplication->camelize($segment);
    }
    $elnClassname .= 'View';
    if(file_exists($elnApplication->projectFile($elnFilename)) === false){
      throw new Eln_Exception(_('view file "{$1}" not found.'), $elnFilename);
    }
    /**/
    include_once(ELNATH_ROOT . '/lib/eln.view.php');
    $elnDirectory = 'views';
    $elnView = $elnDirectory . '/view.php';
    if(file_exists($elnApplication->projectFile($elnView))){
      include_once($elnView);
    }
    foreach(explode('/', $elnOutput) as $segment){
      $elnDirectory .= '/' . $segment;
      $elnView = $elnDirectory . '/view.php';
      if(file_exists($elnApplication->projectFile($elnView))){
	include_once($elnView);
      }
    }
    include_once($elnFilename);
    if(class_exists($elnClassname) === false){
      throw new Eln_Exception(_('view class "{$1}" not found.'), $elnClassname);
    }
    $elnViewer = new $elnClassname();
    $elnViewer->render();
  }catch(Eln_Redirection $e){
    /* リダイレクト */
    header(sprintf('Location: %s', $e->getUri()), true, 303);
  }catch(Eln_Direction $e){
    /* データ送信（ビュー不要） */
    ;
  }
}catch(Eln_404 $e){
  /* 404 */
  header("HTTP/1.0 404 Not Found");
  $eln404 = ELN_PUBLIC_ROOT . DIRECTORY_SEPARATOR . '_error-404.php';
  if(file_exists($eln404)){
    include($eln404);
  }else{
    echo "404 Not Found";
  }
}catch(Exception $e){
  /* エラー */
  $elnError = ELN_PUBLIC_ROOT . DIRECTORY_SEPARATOR . '_error.php';
  if(file_exists($elnError)){
    include($elnError);
  }else{
    echo '[ERROR!!]<br>';
    echo $e->getMessage() . '<br>';
    echo '<br>';
    echo debug_print_backtrace();
  }
}
