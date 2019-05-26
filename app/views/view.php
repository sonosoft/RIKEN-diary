<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * view.php
 */


class View extends Eln_View {
  /*
   * ビューファイルを初期化する
   */
  protected function beforeRender($renderer){
    /**/
    parent::beforeRender($renderer);

    /**/
    // $renderer->pageTitle = 'タイトル';
    // $renderer->layoutTemplate = 'layout.html';
    // $renderer->addStylesheetFiles('css/common.css', 'css/page.css');
    // $renderer->addJavascriptFiles('js/jquery.min.js', 'js/common.js');
    /**/
    // $renderer->databaseLog = true;
    // $renderer->databaseLogs = Eln_Database::$logs;
  }
}
