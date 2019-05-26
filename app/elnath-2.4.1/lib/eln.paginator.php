<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * eln.paginator.php
 */


/*
 * ペジネータクラス
 */
final class Eln_Paginator{
  /*
   * プロパティ
   */
  public $itemCount;
  public $pageCount;
  public $pageSize;
  public $currentPage;
  public $startPage;
  public $endPage;
  public $startItem;
  public $endItem;
  public $previousPage;
  public $nextPage;
  public $previousCount;
  public $nextCount;
  public $pages;

  /* ===== ===== */

  /*
   * コンストラクタ
   */
  public function __construct($itemCount, $options){
    /**/
    $options = array_merge(array('pageSize'=>10, 'indexSize'=>10), $options);

    /* プロパティを初期化 */
    $this->itemCount = max($itemCount, 0);
    $this->pageSize = max(intval($options['pageSize']), 1);
    $indexSize = max(intval($options['indexSize']), 1);

    /* 全ページ数 */
    if($this->itemCount > 0){
      $this->pageCount = intval(($this->itemCount + ($this->pageSize - 1)) / $this->pageSize);
    }else{
      $this->pageCount = 1;
    }

    /* 現在ページ数 */
    if(isset($options['page'])){
      $page = intval($options['page']);
    }else{
      $page = 1;
    }
    $page = max($page, 1);
    $page = min($page, $this->pageCount);
    $this->currentPage = $page;

    /* レコード */
    if($this->itemCount > 0){
      $this->startItem = $this->pageSize * ($this->currentPage - 1) + 1;
      $this->endItem = min($this->startItem + ($this->pageSize - 1), $this->itemCount);
    }else{
      $this->startItem = 0;
      $this->endItem = 0;
    }

    /* インデックス */
    if($this->pageCount <= $indexSize){
      $this->startPage = 1;
      $this->endPage = $this->pageCount;
    }else{
      $this->startPage = $this->currentPage - intval($indexSize / 2);
      $this->endPage = $this->startPage + ($indexSize - 1);
      if($this->startPage < 1){
        $diff = 1 - $this->startPage;
        $this->startPage = 1;
        $this->endPage = min($this->endPage + $diff, $this->pageCount);
      }else if($this->endPage > $this->pageCount){
        $diff = $this->endPage - $this->pageCount;
        $this->startPage = max($this->startPage - $diff, 1);
        $this->endPage = $this->pageCount;
      }
    }

    /**/
    $this->previousCount = max(min($this->startItem - 1, $this->pageSize), 0);
    $this->nextCount = min($this->itemCount - $this->endItem, $this->pageSize);
    $this->previousPage = $this->currentPage - 1;
    $this->nextPage = ($this->nextCount > 0 ? ($this->currentPage + 1) : 0);
    $this->pages = range($this->startPage, $this->endPage);
  }
}
