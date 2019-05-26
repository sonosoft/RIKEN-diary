<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * eln.object.php
 */


/*
 * 基本クラス
 */
abstract class Eln_Object {
  /*
   * プロパティ
   */
  protected $app;
  protected $router;
  /**/
  private $models;
  private $validators;
  private $modules;

  /* ===== ===== */

  /*
   * コンストラクタ
   */
  protected function __construct(){
    /* アプリケーションインスタンス */
    $this->app = Eln_Application::getInstance();

    /* URIルーターインスタンス */
    $this->router = Eln_Router::getInstance();

    /* プロパティ */
    $this->models = array();
    $this->validators = array();
    $this->modules = array();
  }

  /*
   * モデル
   */
  final protected function useModel(){
    $models = func_get_args();
    foreach($models as $name){
      if(in_array($name, $this->models) === false){
	$file = 'models/' . $this->app->decamelize($name) . '_model.php';
	$model = $name . 'Model';
	$factory = $name . 'ModelFactory';
	if(file_exists($this->app->projectFile($file)) === false){
	  throw new Eln_Exception(_('could not find model file "{$1}".'), $file);
	}
	include_once($file);
	if(class_exists($factory) === false){
	  throw new Eln_Exception(_('model factory class "{$1}" is not defined.'), $factory);
	}
	$this->$model = Eln_ModelFactory::getInstance($factory);
	$this->models[] = $name;
      }
    }
  }

  /*
   * バリデータ
   */
  final protected function useValidator(){
    $validators = func_get_args();
    foreach($validators as $name){
      if(in_array($name, $this->validators) === false){
	$file = 'validators/' . $this->app->decamelize($name) . '_validator.php';
	$validator = $name . 'Validator';
	if(file_exists($this->app->projectFile($file)) === false){
	  throw new Eln_Exception(_('could not find validator file "{$1}".'), $file);
	}
	include_once($file);
	if(class_exists($validator) === false){
	  throw new Eln_Exception(_('validator class "{$1}" is not defined.'), $validator);
	}
	$this->$validator = Eln_Validator::getInstance($validator);
	$this->validators[] = $name;
      }
    }
  }

  /*
   * モジュール
   */
  final protected function useModule(){
    $modules = func_get_args();
    foreach($modules as $name){
      if(in_array($name, $this->modules) === false){
	$file = ELNATH_ROOT . '/lib/mod/eln.' . $this->app->decamelize($name) . '.php';
	$moduleProperty = $name . 'Module';
	$moduleClass = 'Eln_' . $moduleProperty;
	if(file_exists($this->app->projectFile($file)) === false){
	  throw new Eln_Exception(_('could not find module file "{$1}".'), $file);
	}
	include_once($file);
	if(class_exists($moduleClass) === false){
	  throw new Eln_Exception(_('module class "{$1}" is not defined.'), $moduleClass);
	}
	$this->$moduleProperty = new $moduleClass();
	$this->modules[] = $name;
      }
    }
  }
}
