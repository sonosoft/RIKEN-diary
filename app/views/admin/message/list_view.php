<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/message/list_view.php
 */


class AdminMessageListView extends Eln_View {
  /*
   * ビューファイルを初期化する
   */
  protected function beforeRender($renderer){
    /**/
    parent::beforeRender($renderer);

    /**/
    $renderer->layoutTemplate = 'admin/message/list.html';
  }
}
