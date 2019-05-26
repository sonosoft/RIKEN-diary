<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/project/list_view.php
 */


class AdminProjectListView extends AdminProjectView {
  /*
   * ビューファイルを初期化する
   */
  protected function beforeRender($renderer){
    /**/
    parent::beforeRender($renderer);

    /**/
    // $renderer->pageTitle = 'タイトル';
    /**/
    // $renderer->layoutTemplate = 'admin/project/list.html';
    // $renderer->innerTemplate = 'admin/project/list.html';
  }
}
