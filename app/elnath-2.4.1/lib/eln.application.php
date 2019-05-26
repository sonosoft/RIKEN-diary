<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * eln.application.php
 */


/*
 * アプリケーションクラス
 */
class Eln_Application {
  /*
   * 設定
   */
  public static $name = 'elnath';
  public static $mode = 'debug';
  public static $settings = null;

  /*
   * プロパティ
   */
  public $sessionName;
  public $sessionId;
  public $route;
  public $data;
  public $errors;
  /**/
  private $isGet;
  private $isPost;
  private $request;
  private $dataExistence;
  /**/
  private static $instance = null;

  /* ===== ===== */

  /*
   * シングルトン
   */
  public static function getInstance(){
    if(self::$instance === null){
      if(class_exists('Application')){
	self::$instance = new Application();
      }else{
	self::$instance = new Eln_Application();
      }
    }
    return self::$instance;
  }

  /*
   * コンストラクタ
   */
  protected function __construct(){
    /**/
    $settings = array(
      'locale'=>'ja_JP.UTF-8',
      'timezone'=>'Asia/Tokyo',
      'memory_limit'=>-1,
      'time_limit'=>0,
      'display_errors'=>false,
      'error_reporting'=>E_ALL,
      'session_name'=>'ElnSSID',
    );
    if(isset(self::$settings[self::$mode]) && is_array(self::$settings[self::$mode])){
      $settings = array_merge($settings, self::$settings[self::$mode]);
    }

    /* 環境設定 */
    setlocale(LC_ALL, $settings['locale']);
    ini_set('session.use_trans_sid', 0);
    ini_set('date.timezone', $settings['timezone']);
    ini_set('memory_limit', $settings['memory_limit']);
    set_time_limit($settings['time_limit']);
    ini_set('display_errors', $settings['display_errors']);
    error_reporting($settings['error_reporting']);

    /* プロパティ */
    $this->sessionName = $settings['session_name'];
    $this->sessionId = null;
    $this->route = array();
    $this->data = array();
    $this->errors = array();

    /* リクエスト */
    $this->isGet = false;
    $this->isPost = false;
    if(isset($_SERVER['REQUEST_METHOD'] )){
      if(strcmp($_SERVER['REQUEST_METHOD'], 'GET')){
	$this->isGet = true;
      }else if(strcmp($_SERVER['REQUEST_METHOD'], 'POST')){
	$this->isPost = true;
      }
    }
    /**/
    $this->request = array();
    if(is_array($_REQUEST)){
      foreach($_REQUEST as $name=>$value){
	$this->request[$name] = $value;
      }
    }
    if(is_array($_FILES)){
      foreach($_FILES as $name1=>$value1){
	if(isset($this->request[$name1]) === false){
	  $this->request[$name1] = array();
	}
	if(is_array($value1)){
	  foreach($value1 as $name2=>$value2){
	    if(is_array($value2)){
	      foreach($value2 as $name3=>$value3){
		if(isset($this->request[$name1][$name3]) === false){
		  $this->request[$name1][$name3] = array();
		}
		$this->request[$name1][$name3][$name2] = $value3;
	      }
	    }else{
	      $this->request[$name1][$name2] = $value2;
	    }
	  }
	}
      }
    }

    /* i18n */
    bindtextdomain('messages', $this->projectFile('i18n'));
    textdomain('messages');

    /**/
    $this->onInitialize();
  }
  protected function onInitialize(){
  }

  /*
   * プロパティ
   */
  public function __get($name){
    if(strcmp($name, 'isGet') == 0){
      return $this->isGet;
    }else if(strcmp($name, 'isPost') == 0){
      return $this->isPost;
    }
    return null;
  }

  /* ===== ===== */

  /*
   * リクエスト
   */
  final public function readRequest($name, $default=null){
    if(empty($name)){
      return $default;
    }
    return $this->_readData($this->request, explode('.', $name), $default);
  }

  /*
   * セッション
   */
  final public function restoreSession($name, $default=null){
    if(empty($name)){
      return $default;
    }
    return $this->_readData($_SESSION, explode('.', $name), $default);
  }
  final public function storeSession($name, $value){
    if(empty($name)){
      return;
    }
    $this->_writeData($_SESSION, explode('.', $name), $value);
  }
  /**/
  final public function chooseSession(){
    $keys = func_get_args();
    if(empty($keys)){
      return;
    }
    $session = array();
    foreach($keys as $key){
      if(empty($key) === false){
	$value = $this->_readData($_SESSION, explode('.', $key), null);
	if($this->dataExistence){
	  $this->_writeData($session, explode('.', $key), $value);
	}
      }
    }
    $_SESSION = $session;
  }
  final public function removeSession(){
    $keys = func_get_args();
    if(empty($keys)){
      $_SESSION = array();
      return;
    }
    foreach($keys as $key){
      if(empty($key) === false){
	$this->_removeData($_SESSION, explode('.', $key));
      }
    }
  }

  /*
   * クッキー
   */
  final public function restoreCookie($name, $default=null){
    if(empty($name)){
      return $default;
    }
    if(array_key_exists($name, $_COOKIE)){
      return $_COOKIE[$name];
    }
    return $default;
  }
  final public function storeCookie($name, $value, $expire=2592000){
    if(empty($name)){
      return;
    }
    $_COOKIE[$name] = $value;
    if($expire > 0){
      setcookie($name, $value, time() + $expire, ELN_WWW_ROOT);
    }else{
      setcookie($name, $value, 0, ELN_WWW_ROOT);
    }
  }

  /*
   * 例外を反映
   */
  final public function exportValidation($exception, $name, $errors=null){
    if($exception !== null){
      $this->data[$name] = $exception->getData();
      $this->exportValidation(null, $name, $exception->getErrors());
    }else{
      if(is_array($errors)){
	foreach($errors as $key=>$value){
	  $this->exportValidation(null, $name.'['.$key.']', $value);
	}
      }else{
	$this->errors[$name] = $errors;
      }
    }
  }

  /*
   * ログ出力
   */
  final public function writeLog($label, $error, $name=null){
    if($name !== null){
      $file = $this->logFile(sprintf('%s.log', $name));
    }else{
      $file = $this->logFile('error.log');
    }
    if(($fp = fopen($file, 'a')) !== false){
      fprintf($fp, "===== ===== [%s]<%s> ===== =====", $label, strftime('%Y/%m/%d %H:%M:%S', time()));
      fwrite($fp, PHP_EOL);
      fwrite($fp, $error);
      fwrite($fp, PHP_EOL);
      fwrite($fp, "-----");
      fwrite($fp, PHP_EOL);
      fclose($fp);
    }
  }

  /*
   * ディレクトリ作成
   */
  final public function createDirectory($dirname){
    if(file_exists($dirname) === false){
      $this->createDirectory(dirname($dirname));
      mkdir($dirname);
    }
  }

  /*
   * ディレクトリコピー
   */
  final public function copyDirectory($src, $dst){
    $this->deleteDirectory($dst);
    mkdir($dst);
    if(file_exists($src) && is_dir($src)){
      if(($dir = opendir($src)) !== false){
	while(($name = readdir($dir)) !== false){
	  if(strcmp($name, '.') != 0 && strcmp($name, '..') != 0){
	    $srcpath = $src . DIRECTORY_SEPARATOR . $name;
	    $dstpath = $dst . DIRECTORY_SEPARATOR . $name;
	    if(is_dir($srcpath)){
	      $this->copyDirectory($srcpath, $dstpath);
	    }else{
	      copy($srcpath, $dstpath);
	    }
	  }
	}
	closedir($dir);
      }
    }
  }

  /*
   * ディレクトリ削除
   */
  final public function deleteDirectory($target){
    if(file_exists($target)){
      if(is_dir($target)){
	if(($dir = opendir($target)) !== false){
	  while(($name = readdir($dir)) !== false){
	    if(strcmp($name, '.') != 0 && strcmp($name, '..') != 0){
	      $this->deleteDirectory($target . DIRECTORY_SEPARATOR . $name);
	    }
	  }
	  closedir($dir);
	}
	rmdir($target);
      }else{
	unlink($target);
      }
    }
  }

  /*
   * 公開ファイル
   */
  final public function publicFile($name){
    $pathArray = array();
    foreach(explode('/', $name) as $segment){
      if(strlen($segment) > 0){
        $pathArray[] = $segment;
      }
    }
    array_unshift($pathArray, ELN_PUBLIC_ROOT);

    /**/
    return implode(DIRECTORY_SEPARATOR, $pathArray);
  }

  /*
   * プロジェクトファイル
   */
  final public function projectFile($name){
    $pathArray = array();
    foreach(explode('/', $name) as $segment){
      if(strlen($segment) > 0){
        $pathArray[] = $segment;
      }
    }
    array_unshift($pathArray, ELN_PROJECT_ROOT);

    /**/
    return implode(DIRECTORY_SEPARATOR, $pathArray);
  }

  /*
   * ログファイル
   */
  final public function logFile($name){
    return $this->projectFile('var/log/'.$name);
  }

  /*
   * キャッシュファイル
   */
  final public function cacheFile($name){
    return $this->projectFile('var/cache/'.$name);
  }

  /*
   * 一時ファイル
   */
  final public function temporaryFile($name){
    return $this->projectFile('var/tmp/'.$name);
  }

  /*
   * CamelCase文字列に変換する
   */
  final public function camelize($str, $heading=true){
    if($heading){
      $str = preg_replace_callback('/^([a-z])/', function($matches){ return strtoupper($matches[1]); }, $str);
    }
    return preg_replace_callback('/_([a-z])/', function($matches){ return strtoupper($matches[1]); }, $str);
  }

  /*
   * CamelCase文字列を復元する
   */
  final public function decamelize($str){
    $str = preg_replace_callback('/^([A-Z])/', function($matches){ return strtolower($matches[1]); }, $str);
    return preg_replace_callback('/([A-Z])/', function($matches){ return '_'.strtolower($matches[1]); }, $str);
  }

  /* ===== ===== */

  /*
   * データ読み込み
   */
  private function _readData(&$data, $path, $default){
    /**/
    $name = array_shift($path);

    /**/
    if(is_array($data) && array_key_exists($name, $data)){
      if(empty($path)){
	$this->dataExistence = true;
	return $data[$name];
      }
      return $this->_readData($data[$name], $path, $default);
    }
    /**/
    $this->dataExistence = true;
    return $default;
  }

  /*
   * データ書き出し
   */
  private function _writeData(&$data, $path, $value){
    /**/
    if(empty($path)){
      return;
    }
    $name = array_shift($path);
  
    /**/
    if(is_array($data) === false){
      $data = array();
    }
    if(empty($path)){
      $data[$name] = $value;
      return;
    }
    if(array_key_exists($name, $data) === false){
      $data[$name] = array();
    }
    $this->_writeData($data[$name], $path, $value);
  }

  /*
   * データ削除
   */
  private function _removeData(&$data, $path){
    /**/
    if(empty($path)){
      return;
    }
    $name = array_shift($path);

    /**/
    if(is_array($data) && array_key_exists($name, $data)){
      if(empty($path)){
	unset($data[$name]);
	return;
      }
      return $this->_removeData($data[$name], $path);
    }
  }
}


/*
 * グローバル関数
 */
function load_controller(){
  $models = func_get_args();
  foreach($models as $name){
    if(in_array($name, $this->models) === false){
      $file = 'controllers/' . $name . '_controller.php';
      if(file_exists($this->app->projectFile($file)) === false){
	throw new Eln_Exception(_('could not find controller file "{$1}".'), $file);
      }
      include_once($file);
    }
  }
}
/**/
function load_view(){
  $models = func_get_args();
  foreach($models as $name){
    if(in_array($name, $this->models) === false){
      $file = 'views/' . $name . '_view.php';
      if(file_exists($this->app->projectFile($file)) === false){
	throw new Eln_Exception(_('could not find view file "{$1}".'), $file);
      }
      include_once($file);
    }
  }
}
