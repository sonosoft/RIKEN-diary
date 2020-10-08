<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/restoration/receipt_view.php
 */


class AdminRestorationReceiptView extends Eln_View {
  /*
   * ビューファイルを初期化する
   */
  protected function beforeRender($renderer){
    /**/
    parent::beforeRender($renderer);

    /**/
    $renderer->layoutTemplate = 'admin/restoration/receipt.html';
  }
}
