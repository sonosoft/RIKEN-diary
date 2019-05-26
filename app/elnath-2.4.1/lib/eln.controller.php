<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * eln.controller.php
 */


/*
 * コントローラ基本クラス
 */
abstract class Eln_Controller extends Eln_Object {
  /*
   * コンストラクタ
   */
  public function __construct(){
    parent::__construct();
  }

  /*
   * アクションを実行する
   */
  final public function execute($action=null){
    /* コールバックメソッド */
    $this->beforeSession();

    /* セッションを開始 */
    if($this->app->sessionName !== null){
      session_name($this->app->sessionName);
    }
    $this->app->sessionName = session_name();
    if($this->app->sessionId === null && isset($_REQUEST[$this->app->sessionName])){
      $this->app->sessionId = $_REQUEST[$this->app->sessionName];
    }
    if($this->app->sessionId !== null){
      session_id($this->app->sessionId);
    }
    session_start();
    $this->app->sessionId = session_id();

    /* コールバックメソッド */
    $this->beforeAction();

    /* アクションメソッドを実行 */
    if($action !== null){
      $method = $action . '_action';
      $result = $this->$method();
    }else{
      $result = $this->action();
    }

    /* コールバックメソッド */
    $this->afterAction();

    /**/
    return $result;
  }

  /*
   * アクション
   */
  public function action(){
    return null;
  }

  /* ===== ===== */

  /*
   * リダイレクトする
   */
  final protected function redirect($statement, $parameters=null, $protocol=false, $port=null){
    throw new Eln_Redirection($this->router->uri($statement, $parameters, $protocol, $port));
  }

  /*
   * ビュー表示をキャンセルする
   */
  final protected function direct(){
    throw new Eln_Direction();
  }

  /* ===== =====*/

  /*
   * 初期化処理を実行する
   */
  protected function beforeSession(){}

  /*
   * アクション実行前処理を実行する
   */
  protected function beforeAction(){}

  /*
   * アクション実行後レンダリング前処理を実行する
   */
  protected function afterAction(){}
}
