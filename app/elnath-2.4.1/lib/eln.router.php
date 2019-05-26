<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * eln.application.php
 */


/*
 * URLルータクラス
 */
final class Eln_Router {
  /*
   * 定数
   */
  const SESSION_AUTO = 0;
  const SESSION_COOKIE = 1;
  const SESSION_URL = 2;

  /*
   * ルート定義
   */
  public static $routes = null;
  public static $session = self::SESSION_AUTO;
  
  /*
   * シングルトンインスタンス
   */
  private static $instance = null;
  
  /*
   * 定義済コントローラ
   */
  private $controllersDir;
  private $controllers = null;

  /* ===== ===== */

  /*
   * シングルトン
   */
  public static function getInstance(){
    if(self::$instance === null){
      self::$instance = new Eln_Router();
    }
    return self::$instance;
  }

  /*
   * コンストラクタ
   */
  private function __construct(){
    /* 定義済コントローラを取得 */
    $this->controllersDir = ELN_PROJECT_ROOT . DIRECTORY_SEPARATOR . 'controllers';
    $this->controllers = array();
    $this->parseControllers('');
  }

  /* ===== ===== */

  /*
   * URIをルート解析する
   */
  public function parse(){
    $webRoot = dirname($_SERVER['SCRIPT_NAME']);
    $uriPath = substr($_SERVER['REQUEST_URI'], strlen($webRoot));
    if(($pos = strpos($uriPath, '?')) !== false){
      $uriPath = substr($uriPath, 0, $pos);
    }
    foreach(self::$routes as $name=>$route){
      if(($result = $this->parseUri($uriPath, $route)) !== false){
	$result['route'] = $name;
	return $result;
      }
    }
    return false;
  }

  /*
   * URIを生成する
   */
  public function uri($statement, $query=array(), $protocol=false, $port=false){
    /**/
    $routeName = null;
    $routeArray = array();
    $parameters = array();
    $anchor = null;

    /* パラメータ */
    if(is_array($query)){
      foreach($query as $name=>$value){
	$parameters[$name] = $value;
      }
    }else if(is_string($query)){
      $query = trim($query);
      if(strlen($query) > 0){
	$ps = explode('&', $query);
	foreach($ps as $segment){
	  $expression = explode('=', $segment, 2);
	  $parameters[$expression[0]] = (isset($expression[1]) ? $expression[1] : null);
	}
      }
    }

    /**/
    if(is_array($statement)){
      /* URIルート配列 */
      $routeArray = $statement;
      if(isset($routeArray['name'])){
	$routeName = $routeArray['name'];
	unnset($routeArray['name']);
      }
    }else if(is_string($statement)){
      $statement = trim($statement);
      if(preg_match('/^(https?|ftp):\/\/([^\?]+)(?:\?(.*))?$/', $statement, $matches)){
	/* 汎用URI */
	$protocol = $matches[1];
	$address = $matches[2];
	if(isset($matches[3]) && strlen($matches[3]) > 0){
	  foreach(explode('&', $matches[3]) as $segment){
	    $expression = explode('=', $segment, 2);
	    if(isset($parameters[$expression[0]]) === false){
	      $parameters[$expression[0]] = (isset($expression[1]) ? $expression[1] : null);
	    }
	  }
	}
	$queries = array();
	foreach($parameters as $key=>$value){
	  if($value !== null && $value !== false){
	    $queries[] = $key . '=' . urlencode($value);
	  }else{
	    $queries[] = $key . '=';
	  }
	}
	$uri = $protocol . '://' . $address;
	if(empty($queries) === false){
	  $uri .= '?' . implode('&', $queries);
	}
	return $uri;
      }else if(preg_match('/^([^:]+):(.*)$/', $statement, $matches)){
	/* ルート定義 */
	$routeName = $matches[1];
	$castr = null;
	$query = null;
	/**/
	if(isset($matches[2]) && strlen($matches[2]) > 0){
	  $apos = strpos($matches[2], '#');
	  $qpos = strpos($matches[2], '?');
	  if($apos !== false && $qpos !== false){
	    if($apos < $qpos){
	      $anchor = substr($matches[2], $apos + 1, $qpos - ($apos + 1));
	      $castr = substr($matches[2], 0, $apos);
	      $query = substr($matches[2], $qpos + 1);
	    }else{
	      $anchor = substr($matches[2], $apos + 1);
	      $castr = substr($matches[2], 0, $qpos);
	      $query = substr($matches[2], $qpos + 1, $apos - ($qpos + 1));
	    }
	  }else if($apos !== false){
	    $anchor = substr($matches[2], $apos + 1);
	    $castr = substr($matches[2], 0, $apos);
	  }else if($qpos !== false){
	    $castr = substr($matches[2], 0, $qpos);
	    $query = substr($matches[2], $qpos + 1);
	  }else{
	    $castr = $matches[2];
	  }
	}
	if($castr !== null && strlen($castr) > 0){
	  $caarr = explode('.', $castr, 2);
	  if(strlen($caarr[0]) > 0){
	    $routeArray['controller'] = $caarr[0];
	  }
	  if(isset($caarr[1]) && strlen($caarr[1]) > 0){
	    $routeArray['action'] = $caarr[1];
	  }
	}
	if($query !== null){
	  foreach(explode('&', $query) as $segment){
	    $expression = explode('=', $segment, 2);
	    $parameters[$expression[0]] = (isset($expression[1]) ? $expression[1] : null);
	  }
	}
      }else{
	/* ルートパス */
	return $this->completeUri($statement, $protocol, $port);
      }
    }

    /* URI */
    $route = array();
    if(strlen($statement) > 0){
      if($routeName === null){
	throw new Eln_Exception(_('URI route name is not specified.'));
      }else if(isset(self::$routes[$routeName]['uri']) === false){
	throw new Eln_Exception(_('URI route "{$1}" not found.'), $routeName);
      }
      if(isset(self::$routes[$routeName]['defaults']) !== false){
	foreach(self::$routes[$routeName]['defaults'] as $name=>$value){
	  $route[$name] = $value;
	}
      }
      foreach($routeArray as $name=>$value){
	$route[$name] = $value;
      }
      foreach($parameters as $name=>$value){
	$route[$name] = $value;
      }
      if(isset($route['controller']) === false || $route['controller'] === null){
	throw new Eln_Exception(_('controller is not specified.'));
      }
      if(isset($route['action']) === false || $route['action'] === null){
	throw new Eln_Exception(_('action is not specified.'));
      }
    }
    if(!ini_get('session.use_trans_sid') && defined('SID') && is_string(SID) && strlen(SID) > 0){
      if(count(($sa = explode('=', SID))) == 2){
	if(self::$session == self::SESSION_URL){
	  $route[$sa[0]] = $sa[1];
	}else if(self::$session == self::SESSION_AUTO){
	  if(isset($_COOKIE[$sa[0]]) === false || strlen($_COOKIE[$sa[0]]) == 0){
	    $route[$sa[0]] = $sa[1];
	  }
	}
      }
    }
    if(strcasecmp(session_cache_limiter(), 'nocache') != 0){
      $route[sprintf('_%d%03d', time(), mt_rand(1, 999))] = false;
    }
    /**/
    if(strlen($statement) > 0){
      $uri = self::$routes[$routeName]['uri'];
      $routex = array();
      foreach($route as $name=>$value){
	$key = ':' . $name;
	if(strpos($uri, $key) !== false){
	  if(strcasecmp($name, 'controller') == 0 || strcasecmp($name, 'action') == 0){
	    $uri = str_replace($key, $value, $uri);
	  }else{
	    $uri = str_replace($key, urlencode($value), $uri);
	  }
	}else if(strcasecmp($name, 'controller') != 0 && strcasecmp($name, 'action') != 0){
	  $routex[$name] = $value;
	}
      }
    }else{
      $uri = '';
      $routex = $route;
    }
    if(empty($routex) === false){
      $queries = array();
      foreach($routex as $name=>$value){
	if($value !== null && $value !== false){
	  $queries[] = $name . '=' . urlencode($value);
	}else{
	  $queries[] = $name;
	}
      }
      $uri .= '?' . implode('&', $queries);
    }
    if($anchor !== null){
      $uri .= '#' . $anchor;
    }

    /**/
    return $this->completeUri($uri, $protocol, $port);
  }

  /*
   * URI相対パスを生成する
   */
  public function path($statement, $protocol=false, $port=false, $timestamp=true){
    $path = trim($statement);
    if(preg_match('/^(https?|ftp):\/\/([^\?]+)(?:\?(.*))?$/', $path)){
      return $path;
    }
    if($timestamp){
      $filename = Eln_Application::getInstance()->publicFile(ltrim($path, '/'));
      if(file_exists($filename)){
	$path .= '?' . strval(filemtime($filename));
      }
    }
    return $this->completeUri($path, $protocol, $port);
  }

  /* ===== ===== */

  /*
   * 定義済コントローラを取得する
   */
  private function parseControllers($controllerName){
    //
    $controllerName = trim($controllerName, DIRECTORY_SEPARATOR);
    $dirname = rtrim($this->controllersDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $controllerName;
    if(($dir = opendir($dirname)) !== false){
      $actionFlag = false;
      while(($filename = readdir($dir)) !== false){
	if(strcmp(substr($filename, 0, 1), '.') != 0){
	  $path = $dirname . DIRECTORY_SEPARATOR . $filename;
	  if(is_dir($path)){
	    // サブディレクトリ
	    $this->parseControllers($controllerName . DIRECTORY_SEPARATOR . $filename);
	  }else if(strcmp(substr($filename, -11), '_action.php') == 0){
	    // アクションファイル
	    $actionFlag = true;
	  }
	}
      }
      closedir($dir);
      if($actionFlag && strlen($controllerName) > 0){
	// アクションファイルを含むコントローラディレクトリ（空コントローラは対象外）
	$this->controllers[] = str_replace(DIRECTORY_SEPARATOR, '/', $controllerName);
      }
    }
  }

  /*
   * URLをルート解析する
   */
  function parseUri($uri, $route){
    //
    $result = array('controller'=>null, 'action'=>null);
    if(isset($route['defaults']) && is_array($route['defaults'])){
      foreach($route['defaults'] as $key=>$value){
	$result[$key] = $value;
      }
    }

    //
    $uri = trim($uri, '/');
    if(empty($uri)){
      $uriSegments = array();
    }else{
      $uriSegments = explode('/', trim($uri, '/'));
    }
    $routeSegments = explode('/', trim($route['uri'], '/'));
    while(($segment = array_shift($routeSegments)) !== null){
      if(strcmp(substr($segment, 0, 1), ':') == 0){
	// ルートキー
	$routeKey = substr($segment, 1);
	if(strcasecmp($routeKey, 'controller') == 0){
	  // コントローラ
	  $matched = false;
	  if(empty($uriSegments)){
	    if(array_key_exists('controller', $route['defaults'])){
	      $result['controller'] = $route['defaults']['controller'];
	      $matched = true;
	    }
	  }else{
	    foreach($this->controllers as $controller){
	      $n = substr_count($controller, '/') + 1;
	      if(strcasecmp($controller, implode('/', array_slice($uriSegments, 0, $n, true))) == 0){
		$result['controller'] = $controller;
		while($n > 0){
		  array_shift($uriSegments);
		  -- $n;
		}
		$matched = true;
		break;
	      }
	    }
	  }
	  if($matched === false){
	    return false;
	  }
	}else if(strcasecmp($routeKey, 'action') == 0){
	  // アクション
	  $matched = false;
	  if(empty($uriSegments)){
	    if(array_key_exists('action', $route['defaults'])){
	      $result['action'] = $route['defaults']['action'];
	      $matched = true;
	    }
	  }else{
	    if(preg_match('/^[a-z][_0-9a-z]*$/i', $uriSegments[0])){
	      $result['action'] = array_shift($uriSegments);
	      $matched = true;
	    }
	  }
	  if($matched === false){
	    return false;
	  }
	}else if(array_key_exists($routeKey, $route['patterns'])){
	  // パターン
	  $matched = false;
	  if(empty($uriSegments)){
	    if(array_key_exists($routeKey, $route['defaults'])){
	      $result[$routeKey] = $route['defaults'][$routeKey];
	      $matched = true;
	    }
	  }else{
	    if(strcmp(substr($route['patterns'][$routeKey], 0, 1), '/') == 0 &&
	       strcmp(substr($route['patterns'][$routeKey], -1), '/') == 0){
	      // パターンマッチ
	      if(preg_match($route['patterns'][$routeKey], $uriSegments[0])){
		$result[$routeKey] = array_shift($uriSegments);
		$matched = true;
	      }
	    }else if(strcmp($route['patterns'][$routeKey], '*') == 0){
	      // ワイルドカード
	      $result[$routeKey] = implode('/', $uriSegments);
	      $uriSegments = array();
	      $matched = true;
	    }else if(strcasecmp($route['patterns'][$routeKey], 'int') == 0 ||
		     strcasecmp($route['patterns'][$routeKey], 'integer') == 0){
	      // 整数
	      if(ctype_digit($uriSegments[0])){
		$result[$routeKey] = intval(array_shift($uriSegments));
		$matched = true;
	      }
	    }else if(strcasecmp($route['patterns'][$routeKey], $uriSegments[0]) == 0){
	      // 完全一致
	      $result[$routeKey] = array_shift($uriSegments);
	      $matched = true;
	    }
	  }
	  if($matched === false){
	    return false;
	  }
	}else{
	  // その他
	  if(empty($uriSegments)){
	    return false;
	  }
	  $result[$routeKey] = array_shift($uriSegments);
	}
      }else{
	// 文字列
	if(empty($uriSegments) === false && strcmp($segment, $uriSegments[0]) == 0){
	  array_shift($uriSegments);
	}else{
	  return false;
	}
      }
    }
    if(empty($uriSegments) === false){
      return false;
    }

    // アクションファイルをチェック
    $php
      = $this->controllersDir
      . DIRECTORY_SEPARATOR
      . str_replace('/', DIRECTORY_SEPARATOR, $result['controller'])
      . DIRECTORY_SEPARATOR
      . $result['action']
      . '_action.php';
    if(file_exists($php)){
      return $result;
    }

    //
    return false;
  }

  /*
   * URIを生成する
   */
  private function completeUri($uri, $protocol, $port){
    if($protocol === false){
      return ELN_WWW_ROOT . ltrim($uri, '/');
    }else{
      if($port === false){
	$host = ELN_WWW_HOST;
      }else{
	if(($pos = strpos(ELN_WWW_HOST, ':')) !== false){
	  $host = substr(ELN_WWW_HOST, 0, $post) . ':' . $port;
	}else{
	  $host = ELN_WWW_HOST . ':' . $port;
	}
      }
    }
    if($protocol === true){
      return ELN_WWW_PROTOCOL . $host . ELN_WWW_ROOT . ltrim($uri, '/');
    }
    return $protocol . $host . ELN_WWW_ROOT . ltrim($uri, '/');
  }
}
