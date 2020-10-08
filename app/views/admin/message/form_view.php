<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/message/form_view.php
 */


class AdminMessageFormView extends Eln_View {
  /*
   * ビューファイルを初期化する
   */
  protected function beforeRender($renderer){
    /**/
    parent::beforeRender($renderer);

    /**/
    $renderer->layoutTemplate = 'admin/message/form.html';
  }
}
