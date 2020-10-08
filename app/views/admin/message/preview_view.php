<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/message/preview_view.php
 */


class AdminMessagePreviewView extends Eln_View {
  /*
   * ビューファイルを初期化する
   */
  protected function beforeRender($renderer){
    /**/
    parent::beforeRender($renderer);

    /**/
    $renderer->layoutTemplate = 'admin/message/preview.html';
  }
}
