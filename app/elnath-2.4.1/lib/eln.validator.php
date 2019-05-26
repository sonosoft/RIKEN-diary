<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * eln.validator.php
 */


/*
 * バリデータクラス
 */
abstract class Eln_Validator extends Eln_Object {
  /*
   * プロパティ
   */
  private $data;
  private $errors;
  private $encoding;
  private $stack;
  /**/
  private static $instances = array();

  /* ===== ===== */

  /*
   * インスンス
   */
  public static function getInstance($name){
    $key = strtolower($name);
    if(empty(self::$instances[$key])){
      self::$instances[$key] = new $name();
    }
    return self::$instances[$key];
  }

  /* ===== ===== */

  /*
   * データを検証する
   */
  final public function validate($data){
    /* エラーを初期化 */
    if(is_array($data)){
      $this->data = $data;
    }
    $this->errors = array();

    /* データを検証 */
    $args = func_get_args();
    call_user_func_array(array($this, 'validateData'), array_slice($args, 1));
    /**/
    if(count($this->errors) > 0){
      throw new Eln_Validation($this->data, $this->errors);
    }

    /**/
    return $this->data;
  }

  /* ===== ===== */

  /*
   * コンストラクタ
   */
  protected function __construct(){
    /**/
    parent::__construct();

    /* プロパティを初期化 */
    $this->data = array();
    $this->errors = array();
    $this->encoding = mb_internal_encoding();
    $this->stack = array();
  }

  /*
   * データを検証する
   */
  protected function validateData(){}

  /* ===== ===== */

  /*
   * パラメータ名を取得する
   */
  final protected function getKeys(){
    return array_keys($this->data);
  }

  /*
   * 値を取得する
   */
  final protected function getValue($name){
    if(array_key_exists($name, $this->data)){
      return $this->data[$name];
    }
    return null;
  }

  /*
   * 値を設定する
   */
  final protected function setValue($name, $value){
    $this->data[$name] = $value;
  }

  /*
   * 値を消去する
   */
  final protected function unsetValue($name){
    if(array_key_exists($name, $this->data)){
      unset($this->data[$name]);
    }
  }

  /*
   * エラーを取得する
   */
  final protected function getError($name){
    if(array_key_exists($name, $this->errors)){
      return $this->errors[$name];
    }
    return null;
  }

  /*
   * エラーを設定する
   */
  final protected function setError($name, $error){
    $args = func_get_args();
    $parameters = array();
    foreach(array_slice($args, 2) as $index=>$value){
      $parameters[sprintf('{$%d}', $index + 1)] = strval($value);
    }
    $this->errors[$name] = strtr($error, $parameters);
  }

  /*
   * エラーの名称を変更する
   */
  final protected function renameError($srcName, $dstName){
    if(array_key_exists($srcName, $this->errors)){
      $this->errors[$dstName] = $this->errors[$srcName];
      unset($this->errors[$srcName]);
    }
  }

  /*
   * エラーを削除する
   */
  final protected function clearError(){
    $args = func_get_args();
    if(empty($args)){
      $this->errors = array();
    }else{
      foreach($args as $name){
        if(array_key_exists($name, $this->errors)){
          unset($this->errors[$name]);
        }
      }
    }
  }

  /*
   * 検証結果を取得する
   */
  final protected function isValid($name=null){
    if($name === null){
      return (empty($this->errors) ? true : false);
    }
    return (array_key_exists($name, $this->errors) ? false : true);
  }

  /* ===== ===== */

  /*
   * 空の値かどうかを検査する
   */
  final protected function isEmpty($name){
    if(array_key_exists($name, $this->data)){
      if(ctype_digit($this->data[$name]) || is_int($this->data[$name]) || is_numeric($this->data[$name])){
	return false;
      }
      return empty($this->data[$name]);
    }
    return true;
  }

  /*
   * ファイルフィールドかどうかを検査する
   */
  final protected function isFile($name){
    if(array_key_exists($name, $this->data)){
      if(is_array($this->data[$name])){
        if(array_key_exists('error', $this->data[$name]) && is_int($this->data[$name]['error']) &&
	   array_key_exists('size', $this->data[$name]) && is_int($this->data[$name]['size']) &&
	   array_key_exists('name', $this->data[$name]) && is_string($this->data[$name]['name']) &&
	   array_key_exists('tmp_name', $this->data[$name]) && is_string($this->data[$name]['tmp_name'])){
          return true;
        }
      }
    }
    return false;
  }

  /*
   * ファイルフィールドが空かどうかを検査する
   */
  final protected function isFileEmpty($name){
    if($this->isFile($name) && $this->data[$name]['error'] == UPLOAD_ERR_NO_FILE){
      return true;
    }else if($this->isEmpty($name)){
      return true;
    }
    return false;
  }

  /* ===== ===== */

  /*
   * 空の値の場合に代替値を設定する
   */
  final protected function ifEmpty($name, $value){
    if($this->isEmpty($name)){
      $this->data[$name] = $value;
    }
    return $this;
  }

  /*
   * 大文字に変換する
   */
  final protected function convert($name, $option){
    if(array_key_exists($name, $this->data)){
      if(is_string($this->data[$name])){
        $this->data[$name] = mb_convert_kana($this->data[$name], $option, $this->encoding);
      }
    }
    return $this;
  }

  /*
   * 改行コードを変換する
   */
  final protected function convertEOL($name, $nl=PHP_EOL){
    if(array_key_exists($name, $this->data)){
      if(is_string($this->data[$name])){
	$this->data[$name] = str_replace(array("\r\n", "\r", "\n"), $nl, $this->data[$name]);
      }
    }
    return $this;
  }

  /*
   * 大文字に変換する
   */
  final protected function toUpper($name){
    if(array_key_exists($name, $this->data)){
      if(is_string($this->data[$name])){
        $this->data[$name] = strtoupper($this->data[$name]);
      }
    }
    return $this;
  }

  /*
   * 小文字に変換する
   */
  final protected function toLower($name){
    if(array_key_exists($name, $this->data)){
      if(is_string($this->data[$name])){
        $this->data[$name] = strtolower($this->data[$name]);
      }
    }
    return $this;
  }

  /*
   * 前後の空文字を削除する
   */
  final protected function trim($name, $characters=false){
    if(array_key_exists($name, $this->data)){
      if(is_string($this->data[$name])){
	if($characters !== false){
	  $this->data[$name] = trim($this->data[$name], $characters);
	}else{
	  $this->data[$name] = trim($this->data[$name]);
	}
      }
    }
    return $this;
  }

  /*
   * 文字列中の空文字を削除する
   */
  final protected function close($name){
    if(array_key_exists($name, $this->data)){
      if(is_string($this->data[$name])){
	$src = mb_convert_kana($this->data[$name], 's', $this->encoding);
	$this->data[$name] = str_replace(array(' ', "\r", "\n", "\t"), '', $src);
      }
    }
    return $this;
  }

  /* ===== ===== */

  /*
   * 空の値かどうかを検査する
   */
  final protected function notEmpty($name, $error=null){
    if(array_key_exists($name, $this->errors) === false){
      if($this->isEmpty($name)){
	if($error === null){
	  $error = _('Please input a value.');
	}
	$this->setError($name, $error);
      }
    }
    return $this;
  }

  /*
   * 選択済かどうかを検査する
   */
  final protected function selection($name, $choice=null, $error=null){
    if(array_key_exists($name, $this->errors) === false){
      $valid = true;
      if($this->isEmpty($name)){
	$valid = false;
      }else if($choice !== null){
	if(is_array($choice)){
	  if(in_array($this->data[$name], $choice) === false){
	    $valid = false;
	  }
	}else{
	  if($this->data[$name] !== $choice){
	    $valid = false;
	  }
	}
      }
      if($valid === false){
	if($error === null){
	  $error = _('Please select a value.');
	}
	$this->setError($name, $error);
      }
    }
    return $this;
  }

  /*
   * 真偽値に変換する
   */
  final protected function toBoolean($name){
    if($this->isEmpty($name)){
      $this->data[$name] = false;
    }else if($this->data[$name]){
      $this->data[$name] = true;
    }else{
      $this->data[$name] = false;
    }
    return $this;
  }

  /*
   * 整数に変換する
   */
  final protected function toInteger($name, $error=null){
    if(array_key_exists($name, $this->errors) === false){
      if($this->isEmpty($name) === false){
        if(is_int($this->data[$name]) === false){
          if(preg_match('/^[-+]?[0-9]+$/', strval($this->data[$name]))){
            $this->data[$name] = intval($this->data[$name]);
          }else{
	    if($error === null){
	      $error = _('The input must be an integer.');
	    }
	    $this->setError($name, $error);
          }
        }
      }
    }
    return $this;
  }

  /*
   * 実数に変換する
   */
  final protected function toReal($name, $error=null){
    if(array_key_exists($name, $this->errors) === false){
      if($this->isEmpty($name) === false){
        if(is_float($this->data[$name]) === false){
          if(is_int($this->data[$name])){
            $this->data[$name] = floatval($this->data[$name]);
          }else if(preg_match('/^[-+]?(?:[0-9]*\.)?[0-9]+$/', strval($this->data[$name]))){
            $this->data[$name] = floatval($this->data[$name]);
          }else{
	    if($error === null){
	      $error = _('The input must be a number.');
	    }
	    $this->setError($name, $error);
          }
        }
      }
    }
    return $this;
  }

  /*
   * 日時に変換する
   */
  final protected function toDate($name, $error=null){
    if(array_key_exists($name, $this->errors) === false){
      if($this->isEmpty($name) === false){
	if(!($this->data[$name] instanceof Eln_Date)){
	  $result = strtotime(strval($this->data[$name]));
	  if($result !== false){
	    $this->data[$name] = new Eln_Date($result);
	  }else{
	    if($error === null){
	      $error = _('The input is not a valid date.');
	    }
	    $this->setError($name, $error);
	  }
	}
      }
    }
    return $this;
  }

  /*
   * 正しいURIかどうかを検証する
   */
  final protected function uri($name, $error=null){
    if(array_key_exists($name, $this->errors) === false){
      if($this->isEmpty($name) === false){
        if(preg_match('/^(?:https?|ftp):\/\/[-_.!~*\'()a-zA-Z0-9;\/?:@&=+$,%#]+$/', $this->data[$name])){
          $this->data[$name] = strval($this->data[$name]);
        }else{
	  if($error === null){
	    $error = _('The input is not a valid URI.');
	  }
	  $this->setError($name, $error);
        }
      }
    }
    return $this;
  }

  /*
   * 正しいメールアドレスかどうかを検証する
   */
  final protected function email($name, $error=null){
    if(array_key_exists($name, $this->errors) === false){
      if($this->isEmpty($name) === false){
        if(preg_match('/^[0-9a-zA-Z]+[-_\.0-9a-zA-Z]*@[-_0-9a-zA-Z0-9]+(?:\.[-_0-9a-zA-Z]+)+$/', $this->data[$name])) {
          $this->data[$name] = strval($this->data[$name]);
        }else{
	  if($error === null){
	    $error = _('The input is not a valid email address.');
	  }
	  $this->setError($name, $error);
        }
      }
    }
    return $this;
  }

  /*
   * 文字列を比較する
   */
  final protected function pattern($name, $pattern, $error=null){
    if(array_key_exists($name, $this->errors) === false){
      if($this->isEmpty($name) === false){
        if(preg_match($pattern, $this->data[$name])){
          $this->data[$name] = strval($this->data[$name]);
        }else{
	  if($error === null){
	    $error = _('The input is not valid format.');
	  }
	  $this->setError($name, $error);
        }
      }
    }
    return $this;
  }

  /*
   * 文字列の長さを比較する
   */
  final protected function lengthGT($name, $length, $error=null){
    if(array_key_exists($name, $this->errors) === false){
      if($this->isEmpty($name) === false){
        if(mb_strlen($this->data[$name], $this->encoding) <= $length){
	  if($error === null){
	    $error = _('The input must be greater than {$1} characters long.');
	  }
	  $this->setError($name, $error, $length);
        }
      }
    }
    return $this;
  }

  /*
   * 文字列の長さを比較する
   */
  final protected function lengthGE($name, $length, $error=null){
    if(array_key_exists($name, $this->errors) === false){
      if($this->isEmpty($name) === false){
        if(mb_strlen($this->data[$name], $this->encoding) < $length){
	  if($error === null){
	    $error = _('The input must be greater than or equal to {$1} characters long.');
	  }
	  $this->setError($name, $error, $length);
        }
      }
    }
    return $this;
  }

  /*
   * 文字列の長さを比較する
   */
  final protected function lengthLT($name, $length, $error=null){
    if(array_key_exists($name, $this->errors) === false){
      if($this->isEmpty($name) === false){
        if(mb_strlen($this->data[$name], $this->encoding) >= $length){
	  if($error === null){
	    $error = _('The input must be less than {$1} characters long.');
	  }
	  $this->setError($name, $error, $length);
        }
      }
    }
    return $this;
  }

  /*
   * 文字列の長さを比較する
   */
  final protected function lengthLE($name, $length, $error=null){
    if(array_key_exists($name, $this->errors) === false){
      if($this->isEmpty($name) === false){
        if(mb_strlen($this->data[$name], $this->encoding) > $length){
	  if($error === null){
	    $error = _('The input must be less than or equal to {$1} characters long.');
	  }
	  $this->setError($name, $error, $length);
        }
      }
    }
    return $this;
  }

  /*
   * 数値を比較する
   */
  final protected function numberGT($name, $value, $error=null){
    if(array_key_exists($name, $this->errors) === false){
      if($this->isEmpty($name) === false){
        if(is_int($this->data[$name]) || is_float($this->data[$name])){
          if($this->data[$name] <= $value){
	    if($error === null){
	      $error = _('The input must be greater than {$1}.');
	    }
	    $this->setError($name, $error, $value);
          }
        }
      }
    }
    return $this;
  }

  /*
   * 数値を比較する
   */
  final protected function numberGE($name, $value, $error=null){
    if(array_key_exists($name, $this->errors) === false){
      if($this->isEmpty($name) === false){
        if(is_int($this->data[$name]) || is_float($this->data[$name])){
          if($this->data[$name] < $value){
	    if($error === null){
	      $error = _('The input must be greater than or equal to {$1}.');
	    }
	    $this->setError($name, $error, $value);
          }
        }
      }
    }
    return $this;
  }

  /*
   * 数値を比較する
   */
  final protected function numberLT($name, $value, $error=null){
    if(array_key_exists($name, $this->errors) === false){
      if($this->isEmpty($name) === false){
        if(is_int($this->data[$name]) || is_float($this->data[$name])){
          if($this->data[$name] >= $value){
	    if($error === null){
	      $error = _('The input must be less than {$1}.');
	    }
	    $this->setError($name, $error, $value);
          }
        }
      }
    }
    return $this;
  }

  /*
   * 数値を比較する
   */
  final protected function numberLE($name, $value, $error=null){
    if(array_key_exists($name, $this->errors) === false){
      if($this->isEmpty($name) === false){
        if(is_int($this->data[$name]) || is_float($this->data[$name])){
          if($this->data[$name] > $value){
	    if($error === null){
	      $error = _('The input must be less than or equal to {$1}.');
	    }
	    $this->setError($name, $error, $value);
          }
        }
      }
    }
    return $this;
  }

  /*
   * 日時の範囲を検証する
   */
  final protected function dateGT($name, $value, $error=null){
    if(array_key_exists($name, $this->errors) === false){
      if($this->isEmpty($name) === false){
        if($this->data[$name] instanceof Eln_Date){
          if($this->data[$name]->getTime() <= $value->getTime()){
	    if($error ===null){
	      $error = _('The input must be later than {$1}.');
	    }
	    $this->setError($name, $error, $value);
          }
        }
      }
    }
    return $this;
  }

  /*
   * 日時の範囲を検証する
   */
  final protected function dateGE($name, $value, $error=null){
    if(array_key_exists($name, $this->errors) === false){
      if($this->isEmpty($name) === false){
        if($this->data[$name] instanceof ShallotDate){
          if($this->data[$name]->getTime() < $value->getTime()){
	    if($error ===null){
	      $error = _('The input must be later than or equal to {$1}.');
	    }
	    $this->setError($name, $error, $value);
          }
        }
      }
    }
    return $this;
  }

  /*
   * 日時の範囲を検証する
   */
  final protected function dateLT($name, $value, $error=null){
    if(array_key_exists($name, $this->errors) === false){
      if($this->isEmpty($name) === false){
        if($this->data[$name] instanceof ShallotDate){
          if($this->data[$name]->getTime() >= $value->getTime()){
	    if($error === null){
	      $error = _('The input must be earlier than {$1}.');
	    }
	    $this->setError($name, $error, $value);
          }
        }
      }
    }
    return $this;
  }

  /*
   * 日時の範囲を検証する
   */
  final protected function dateLE($name, $value, $error=null){
    if(array_key_exists($name, $this->errors) === false){
      if($this->isEmpty($name) === false){
        if($this->data[$name] instanceof ShallotDate){
          if($this->data[$name]->getTime() > $value->getTime()){
	    if($error === null){
	      $error = _('The input must be earlier than or equal to {$1}.');
	    }
	    $this->setError($name, $error, $value);
          }
        }
      }
    }
    return $this;
  }

  /*
   * 指定された値に含まれるかを適用する
   */
  final protected function in($name, $choices, $error=null){
    if(array_key_exists($name, $this->errors) === false){
      if($this->isEmpty($name) === false){
	if(in_array($this->data[$name], $choices) === false){
	  if($error === null){
	    $error = _('The value is not in specified values.');
	  }
	  $this->setError($name, $error);
        }
      }
    }
    return $this;
  }

  /*
   * ファイルフィールドが空の値の場合に代替値を設定する
   */
  final protected function ifFileEmpty($name, $value){
    if($this->isFileEmpty($name)){
      $this->data[$name] = $value;
    }
    return $this;
  }

  /*
   * アップロードファイルの選択を検証する
   */
  final protected function fileNotEmpty($name, $error=null){
    if(array_key_exists($name, $this->errors) === false){
      $valid = true;
      if($this->isFile($name)){
        if($this->data[$name]['error'] == UPLOAD_ERR_NO_FILE){
	  $valid = false;
        }
      }else if($this->isEmpty($name)){
	$valid = false;
      }
      if($valid === false){
	if($error === null){
	  $error = _('Please select a file.');
	}
	$this->setError($name, $error);
      }
    }
    return $this;
  }

  /*
   * ファイルアップロードのエラーを検証する
   */
  final protected function fileUpload($name, $error=null){
    if(array_key_exists($name, $this->errors) === false){
      if($this->isFile($name)){
        if($this->data[$name]['error'] != UPLOAD_ERR_OK &&
	   $this->data[$name]['error'] != UPLOAD_ERR_NO_FILE){
	  if($error === null){
	    $error = _('An error occured when uploading a file. (error:{$1})');
	  }
          $this->setError($name, $error, $this->data[$name]['error']);
        }
      }else if($this->isEmpty($name) === false){
	if($error === null){
	  $error = _('Please select a file.');
	}
	$this->setError($name, $error);
      }
    }
    return $this;
  }

  /*
   * ファイルの拡張子を検証する
   */
  final protected function fileExtension($name, $extensions, $error=null){
    if(array_key_exists($name, $this->errors) === false){
      if(isset($this->data[$name]['name'])){
	$isValid = false;
	$extension = end(explode('.', $this->data[$name]['name']));
	if(is_array($extensions)){
	  foreach($extensions as $ex){
	    if(strcasecmp($ex, $extension) == 0){
	      $isValid = true;
	      break;
	    }
	  }
	}else{
	  if(strcasecmp($extensions, $extension) == 0){
	    $isValid = true;
	  }
	}
	if($isValid === false){
	  if($error === null){
	    $error = _('Uploaded file has invalid extension "{$1}".');
	  }
          $this->setError($name, $error, $extension);
        }
      }
    }
    return $this;
  }

  /*
   * バリデーションスタック処理
   */
  final protected function doStack($name){
    $this->stack[] = array('name'=>$name, 'data'=>$this->data, 'errors'=>$this->errors);
    if(array_key_exists($name, $this->data) && is_array($this->data[$name])){
      $this->data = $this->data[$name];
    }else{
      $this->data = array();
    }
    $this->errors = array();
  }
  final protected function doneStack(){
    if(empty($this->stack) === false){
      $stack = array_pop($this->stack);
      $innerData = $this->data;
      $innerError = $this->errors;
      $this->data = $stack['data'];
      $this->errors = $stack['errors'];
      $this->data[$stack['name']] = $innerData;
      if(empty($innerError) === false){
	$this->errors[$stack['name']] = $innerError;
      }
    }
  }
}
