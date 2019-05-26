<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * eln.view.php
 */


/*
 * ビュー基底クラス
 */
abstract class Eln_View extends Eln_Object {
  /*
   * コンストラクタ
   */
  public function __construct(){
    parent::__construct();
  }

  /*
   * ビューページを出力する
   */
  final public function render(){
    /* レンダラー */
    $renderer = $this->getRenderer();

    /* コールバック */
    $this->beforeRender($renderer);

    /* テンプレート */
    $renderer->render();
  }

  /* ===== ===== */

  /*
   * 初期化処理を実行する
   */
  protected function beforeRender($renderer){}

  /*
   * レンダラーを取得する
   */
  protected function getRenderer(){
    include_once(ELNATH_ROOT . '/lib/eln.renderer.php');
    $renderer = new Eln_Renderer();
    return $renderer;
  }
}
