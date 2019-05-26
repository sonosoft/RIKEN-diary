<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * eln.database.php
 */


/*
 * データベースクラス
 */
final class Eln_Database {
  /*
   * 接続定義
   */
  public static $databases = null;
  public static $logs = array();

  /*
   * プロパティ
   */
  private $name;
  private $settings;
  private $connection;
  private $transaction;
  /**/
  private static $instances = array();
  private static $currentInstance = null;

  /* ===== ===== */

  /*
   * インスンス
   */
  public static function getDatabase($name){
    $key = strtolower($name);
    if(empty(self::$instances[$key])){
      self::$instances[$key] = new Eln_Database($name);
    }
    return self::$instances[$key];
  }
  public static function getCurrentDatabase(){
    if(self::$currentInstance === null){
      throw new Eln_Exception(_('There is no active database connection.'));
    }
    return self::$currentInstance;
  }
  public static function setCurrentDatabase($name){
    return (self::$currentInstance = self::getDatabase($name));
  }

  /* ===== ===== */

  /*
   * 接続の名称を取得
   */
  public function getName(){
    return $this->name;
  }

  /*
   * テーブル名を取得
   */
  public function getTableName($tableName){
    if(isset($this->settings['table_prefix'])){
      return $this->settings['table_prefix'].$tableName;
    }
    return $tableName;
  }
  
  /*
   * キャッシュの状態を取得
   */
  public function isCaching(){
    return $this->settings['caching'];
  }
  
  /*
   * 接続確認
   */
  public function isConnected(){
    if($this->connection !== null){
      return true;
    }
    return false;
  }

  /*
   * トランザクション確認
   */
  public function isInTransaction(){
    return $this->transaction;
  }

  /*
   * データベースに接続する
   */
  public function open(){
    /**/
    if($this->connection !== null){
      throw new Eln_Exception(_('database has already been opened.'));
    }

    /* ログ */
    if($this->settings['logging']){
      self::$logs[] = ':open:';
    }

    /* 接続 */
    $connection = new mysqli(
      $this->settings['server'],
      $this->settings['username'],
      $this->settings['password'],
      $this->settings['database']
    );
    if(($errmsg = mysqli_connect_error()) !== null){
      throw new Eln_Exception(_('failed to open database. ({$1})'), $errmsg);
    }

    /* 文字コード */
    if($connection->set_charset($this->settings['charset']) === false){
      $errmsg = $connection->error;
      $connection->close();
      throw new Eln_Exception(_('failed to initialize database connection. ({$1})'), $errmsg);
    }

    /* オートコミット */
    if($connection->autocommit($this->settings['autocommit']) === false){
      $errmsg = $connection->error;
      $connection->close();
      throw new Eln_Exception(_('failed to initialize database connection. ({$1})'), $errmsg);
    }

    /* プロパティ */
    $this->connection = $connection;
    $this->transaction = false;
  }

  /*
   * データベース接続を閉じる
   */
  public function close(){
    /**/
    if($this->connection === null){
      throw new Eln_Exception(_('database has not been opened yet.'));
    }

    /* ログ */
    if($this->settings['logging']){
      self::$logs[] = ':close:';
    }

    /* トランザクション */
    if($this->transaction){
      $this->rollback();
    }

    /* 切断 */
    if($this->connection->close() === false){
      throw new Eln_Exception(_('failed to close database connection. ({$1})'), $this->connection->error);
    }

    /* プロパティ */
    $this->connection = null;
    $this->transaction = false;
  }

  /*
   * トランザクションを開始する
   */
  public function begin(){
    /**/
    if($this->connection === null){
      throw new Eln_Exception(_('database has not been opened yet.'));
    }
    if($this->transaction){
      throw new Eln_Exception(_('transaction has alredy begun.'));
    }

    /* ログ */
    if($this->settings['logging']){
      self::$logs[] = ':begin:';
    }

    /* トランザクション */
    if($this->connection->query('BEGIN') === false){
      throw new Eln_Exception(_('failed to begin transaction. ({$1})'), $this->connection->error);
    }
    $this->transaction = true;
  }

  /*
   * トランザクションをコミットする
   */
  public function commit(){
    /**/
    if($this->connection === null){
      throw new Eln_Exception(_('database has not been opened yet.'));
    }
    if($this->transaction === false){
      throw new Eln_Exception(_('transaction has not begun yet.'));
    }

    /* ログ */
    if($this->settings['logging']){
      self::$logs[] = ':commit:';
    }

    /* コミット */
    if($this->connection->commit() === false){
      throw new Eln_Exception(_('failed to commit transaction. ({$1})'), $this->connection->error);
    }
    $this->transaction = false;
  }

  /*
   * トランザクションをロールバックする
   */
  public function rollback(){
    /**/
    if($this->connection === null){
      throw new Eln_Exception(_('database has not been opened yet.'));
    }
    if($this->transaction === false){
      throw new Eln_Exception(_('transaction has not begun yet.'));
    }

    /* ログ */
    if($this->settings['logging']){
      self::$logs[] = ':rollback:';
    }

    /* ロールバック */
    if($this->connection->rollback() === false){
      throw new Eln_Exception(_('failed to rollback transaction. ({$1})'), $this->connection->error);
    }
    $this->transaction = false;
  }

  /*
   * クエリを実行する
   */
  public function query($statement, $parameters=array()){
    /* 接続を検査 */
    if($this->connection === null){
      throw new Eln_Exception(_('database has not been opened yet.'));
    }

    /* クエリを実行 */
    $statement = $this->buildStatement($statement, $parameters);
    if($this->settings['logging']){	/* ログ */
      self::$logs[] = $statement;
    }
    if(($result = $this->connection->query($statement)) === false){
      throw new Eln_Exception(_('failed to perform a query on database. ({$1})'), $this->connection->error);
    }

    /**/
    return $result;
  }

  /*
   * 最後に自動採番された値を取得する
   */
  public function getInsertId(){
    /* 接続を検査 */
    if($this->connection === null){
      throw new Eln_Exception(_('database has not been opened yet.'));
    }

    /**/
    return $this->connection->insert_id;
  }

  /*
   * 変更レコード数を取得する
   */
  public function countAffectedRows(){
    /* 接続を検査 */
    if($this->connection === null){
      throw new Eln_Exception(_('database has not been opened yet.'));
    }

    /* 変更レコード数を取得 */
    return $this->connection->affected_rows;
  }

  /*
   * 特殊文字をエスケープする
   */
  public function escape($value){
    /* 接続を検査 */
    if($this->connection === null){
      throw new Eln_Exception(_('database has not been opened yet.'));
    }

    /**/
    return $this->connection->real_escape_string($value);

    /**/
    return $value;
  }

  /*
   * 接続オブジェクトを取得する
   */
  public function getMySQLi(){
    /**/
    return $this->connection;
  }

  /* ===== ===== */

  /*
   * コンストラクタ
   */
  private function __construct($name){
    if(isset(self::$databases[$name])){
      $settings = array(
	'server'=>'localhost',
	'username'=>'mysql',
	'password'=>'mysql',
	'database'=>'mysql',
	'charset'=>'utf8',
	'table_prefix'=>'',
	'autocommit'=>false,
	'caching'=>true,
	'logging'=>false,
      );
      if(is_array(self::$databases[$name])){
	$settings = array_merge($settings, self::$databases[$name]);
      }
      /**/
      $this->name = $name;
      $this->settings = $settings;
      $this->connection = null;
      $this->transaction = false;
    }else{
      throw new Eln_Exception(_('could not find database definition "{$1}".'), $name);
    }
  }

  /*
   * SQL文を生成する
   */
  private function buildStatement($statement, $parameters){
    $params = array();
    if(is_array($parameters)){
      foreach($parameters as $key=>$value){
	if(strcmp(substr($key, 0, 1), ':') == 0){
	  $params[$key] = $this->formatParameter($value);
	}else{
	  $params[':' . $key] = $this->formatParameter($value);
	}
      }
    }
    /**/
    return strtr($statement, $params);
  }

  /*
   * パラメータを文字列化する
   */
  private function formatParameter($value){
    if(is_null($value)){
      return 'NULL';
    }else if(is_array($value)){
      $values = array();
      foreach($value as $item){
        $values[] = $this->FormatParameter($item);
      }
      return '(' . implode(', ', $values) . ')';
    }else if(is_string($value) === false && is_numeric($value)){
      return strval($value);
    }else if(is_bool($value)){
      return ($value ? '1' : '0');
    }else if($value instanceof Eln_Date){
      return '"' . $value->format() . '"';
    }
    return '"' . $this->escape(strval($value)) . '"';
  }
}
