<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * work/history/list_view.php
 */


class WorkHistoryListView extends Eln_View {
  /*
   * ビューファイルを初期化する
   */
  protected function beforeRender($renderer){
    /**/
    parent::beforeRender($renderer);

    /**/
    $renderer->pageTitle = '入力確認';
    $renderer->layoutTemplate = 'work/history/list.html';
  }
}
