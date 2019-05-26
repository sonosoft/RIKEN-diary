<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * eln.renderer.php
 */


/*
 * レンダラークラス
 */
class Eln_Renderer {
  /*
   * プロパティ
   */
  public $layoutTemplate;
  public $_;
  /**/
  protected $stylesheetFiles;
  protected $javascriptFiles;
  /**/
  private $__;


  /* ===== ===== */

  /*
   * コンストラクタ
   */
  public function __construct(){
    /**/
    $this->__ = new StdClass();
    $this->__->app = Eln_Application::getInstance();	/* アプリケーションインスタンス */
    $this->__->router = Eln_Router::getInstance();	/* URIルーターインスタンス */

    /* CSS/JS */
    $this->stylesheetFiles = array();
    $this->javascriptFiles = array();

    /* プロパティ */
    $this->layoutTemplate = '';
    $this->_ = array();
    /**/
    foreach($this->__->app->data as $name=>$value){
      $this->$name = $value;
    }
    $this->route = $this->__->app->route;
    $this->errors = $this->__->app->errors;
  }

  /* ===== ===== */

  /*
   * ビューページを出力する
   */
  final public function render($template=null){
    /* テンプレートファイル */
    if($template === null){
      $template = $this->layoutTemplate;
    }
    if(preg_match('/^([^:]+):(.+)$/', $template, $matches)){
      $template = $matches[1];
      $block = $matches[2];
    }else{
      $block = null;
    }
    $html = $this->__->app->projectFile('templates/'.$template);
    /**/
    if(file_exists($html) === false){
      return;
    }

    /* PHP ファイル */
    $php = str_replace(DIRECTORY_SEPARATOR, '-_-', $template);
    $php = $this->__->app->cacheFile('views/eln--'.str_replace('.', '^', $php).'.php');
    $dir = dirname($php);
    if(file_exists($dir) === false){
      mkdir($dir);
    }
    if($block !== null){
      $view = preg_replace('/\.php$/', '~'.$block.'.php', $php);
    }else{
      $view = $php;
    }

    /* コンパイル */
    $compileFlag = false;
    if(file_exists($php) === false){
      $compileFlag = true;
    }else if(filemtime($html) > filemtime($php)){
      $compileFlag = true;
    }
    if($compileFlag){
      include_once(ELNATH_ROOT . '/lib/eln.template.php');
      $converter = new Eln_Template($html, $php);
      $converter->convert();
    }
    if(file_exists($view)){
      include($view);
    }
  }

  /*
   * CSSファイルを設定する
   */
  final public function emptyStylesheetFiles(){
    $this->stylesheetFiles = array();
  }
  final public function addStylesheetFiles(){
    $args = func_get_args();
    foreach($args as $arg){
      $this->stylesheetFiles[] = $this->__->router->path($arg);
    }
  }

  /*
   * JavaScriptファイルを設定する
   */
  final public function emptyJavascriptFiles(){
    $this->javascriptFiles = array();
  }
  final public function addJavascriptFiles(){
    $args = func_get_args();
    foreach($args as $arg){
      $this->javascriptFiles[] = $this->__->router->path($arg);
    }
  }

  /* ===== ===== */

  /*
   * パラメータ評価
   */
  final protected function tEvaluate($name){
    $name = str_replace('[]', '', $name);
    $name = preg_replace('/\[([^\]]+)\]/', '.$1', $name);
    $result = $this;
    foreach(explode('.', $name) as $key){
      if(is_object($result) && isset($result->$key)){
	$result = $result->$key;
      }else if(is_array($result) && isset($result[$key])){
	$result = $result[$key];
      }else{
	return null;
      }
    }
    return $result;
  }

  /*
   * メソッド評価
   */
  final protected function tMethod($name){
    $name = str_replace('[]', '', $name);
    $name = preg_replace('/\[([^\]]+)\]/', '.$1', $name);
    $path = explode('.', $name);
    $method = array_pop($path);
    $result = $this;
    foreach($path as $key){
      if(is_object($result) && isset($result->$key)){
	$result = $result->$key;
      }else if(is_array($result) && isset($result[$key])){
	$result = $result[$key];
      }else{
	$result = null;
	break;
      }
    }
    return array($result, $method);
  }

  /*
   * 代入
   */
  final protected function tAssign($name, $value){
    $name = str_replace('[]', '', $name);
    $name = preg_replace('/\[([^\]]+)\]/', '.$1', $name);
    $this->assignValue($this, explode('.', $name), $value);
  }

  /*
   * 型変換
   */
  final protected function tAsString($value, $format=null){
    if($value instanceof Eln_Date){
      return htmlspecialchars($value->format($format));
    }
    return htmlspecialchars(strval($value));
  }
  final protected function tAsHTML($value, $format=null){
    $out = str_replace('&gt;', '>', str_replace('&lt;', '<', $value));
    $out = str_replace('&quot;', '"', $out);
    $out = str_replace('&amp;', '&', $out);
    return $out;
  }
  final protected function tAsArray($value){
    if(is_array($value)){
      return $value;
    }
    return array();
  }
  final protected function tAsOptions($value){
    $options = array();
    if(is_array($value)){
      foreach($value as $item){
	$data = array('label'=>'');
	if(is_array($item) && isset($item['label'])){
	  $data['label'] = $item['label'];
	}else if(is_array($item) && isset($item['value'])){
	  $data['label'] = $item['value'];
	}else{
	  $data['label'] = strval($item);
	}
	if(is_array($item) && isset($item['options'])){
	  $options[] = $data;
	  $options = array_merge($options, $this->tAsOptions($item['options']));
	  $options[] = array();
	}else{
	  if(is_array($item) && isset($item['value'])){
	    $data['value'] = $item['value'];
	  }else if(is_array($item) && isset($item['label'])){
	    $data['value'] = $item['label'];
	  }else{
	    $data['value'] = strval($item);
	  }
	  $options[] = $data;
	}
      }
    }
    return $options;
  }

  /*
   * チェック判定
   */
  final protected function tIsChecked($name, $value){
    if(($nameValue = $this->tEvaluate($name)) !== null){
      if(strcmp(substr($name, -2), '[]') == 0 && is_array($nameValue)){
	foreach($nameValue as $entry){
	  if(strcmp(strval($entry), strval($value)) == 0){
	    return true;
	  }
	}
      }else if(strcmp(strval($nameValue), strval($value)) == 0){
	return true;
      }
    }else if(strlen(strval($value)) == 0){
      return true;
    }
    return false;
  }

  /* ===== ===== */

  /*
   * 代入
   */
  final protected function template_assign($name, $value){
    $this->tAssign($name, $this->tEvaluate($value));
  }

  /*
   * 評価
   */
  final protected function template_evaluate($name){
    return $this->tEvaluate($name);
  }

  /*
   * URI
   */
  final protected function template_uri($statement, $parameters=null, $protocol=false, $port=null){
    return $this->__->router->uri($statement, $parameters, $protocol, $port);
  }

  /*
   * パス
   */
  final protected function template_path($name, $protocol=false, $port=null, $timestamp=true){
    return $this->__->router->path($name, $protocol, $port, $timestamp);
  }

  /*
   * 日付
   */
  final protected function template_date($date, $format='%Y/%m/%d'){
    if($date instanceof Eln_Date){
      return $date->format($format);
    }
    return strftime($format, intval($date));
  }

  /*
   * 文字列長調整
   */
  final protected function template_truncate($str, $length, $mark='...'){
    if($length > 0 && mb_strlen($str) > $length){
      if(($length -= mb_strlen($mark)) < 0){
	$str = '';
      }else{
	$str = mb_substr($str, 0, $length).$mark;
      }
    }
    return $str;
  }

  /*
   * 条件否定
   */
  final protected function template_not($value){
    return empty($value);
  }

  /* ===== ===== */

  /*
   * パラメータ代入
   */
  private function assignValue(&$data, $path, $value){
    if(empty($path)){
      return;
    }else if(count($path) == 1){
      if(is_object($data)){
	$data->{$path[0]} = $value;
      }else{
	$data[$path[0]] = $value;
      }
      return;
    }
    $key = array_shift($path);
    if(is_object($data)){
      if(isset($data->$key) === false || is_array($data->$key) === false){
	$data->$key = array();
      }
      $this->assignValue($data->$key, $path, $value);
    }else{
      if(isset($data[$key]) === false || is_array($data[$key]) === false){
	$data[$key] = array();
      }
      $this->assignValue($data[$key], $path, $value);
    }
  }
}
