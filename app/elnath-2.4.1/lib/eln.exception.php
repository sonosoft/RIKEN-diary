<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * eln.exception.php
 */


/*
 * 404 not found
 */
final class Eln_404 extends Exception { }


/*
 * リダイレクト
 */
final class Eln_Redirection extends Exception {
  /*
   * プロパティ
   */
  private $uri;

  /* ===== ===== */

  /*
   * コンストラクタ
   */
  public function __construct($uri){
    /**/
    parent::__construct();

    /* URI */
    $this->uri = $uri;
  }

  /*
   * URI
   */
  public function getUri(){
    return $this->uri;
  }
}


/*
 * データ送信（ビュー不要）
 */
final class Eln_Direction extends Exception { }


/*
 * バリデーション
 */
final class Eln_Validation extends Exception {
  /*
   * プロパティ
   */
  private $data;
  private $errors;

  /* ===== ===== */

  /*
   * コンストラクタ
   */
  public function __construct($data, $errors){
    /**/
    parent::__construct();

    /* エラー */
    $this->data = $data;
    $this->errors = $errors;
  }

  /* ===== ===== */

  /*
   * データ
   */
  public function getData(){
    return $this->data;
  }
  public function getDatum($name){
    if(isset($this->data[$name])){
      return $this->data[$name];
    }
    return null;
  }

  /*
   * エラー
   */
  public function getErrors(){
    return $this->errors;
  }
  public function getError($name){
    if(isset($this->errors[$name])){
      return $this->errors[$name];
    }
    return null;
  }
}


/*
 * 例外
 */
final class Eln_Exception extends Exception {
  /*
   * コンストラクタ
   */
  public function __construct(){
    /* 引数リスト */
    $args = func_get_args();
    if(count($args) > 0){
      /* エラーメッセージを構築 */
      $message = array_shift($args);
      $parameters = array();
      foreach($args as $i=>$arg){
        $parameters[sprintf('{$%d}', $i + 1)] = $arg;
      }
      if(count($parameters) > 0){
        $message = strtr($message, $parameters);
      }

      /**/
      parent::__construct($message);
    }else{
      /**/
      parent::__construct();
    }
  }
}
