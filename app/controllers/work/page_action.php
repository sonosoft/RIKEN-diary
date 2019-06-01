<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * work/page_action.php
 */


class WorkPageAction extends WorkController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Page', 'Inquiry', 'System');
    
    /* データ */
    if(($pages = $this->PageModel->load($this->session->system)) === null){
      $this->app->writeLog('work/page', 'invalid session file.');
      $this->redirect('default:work.error');
    }

    /* ページ */
    if(isset($pages[$this->session->page]) === false){
      $this->redirect('default:work.error');
    }
    list($this->app->data['rows'], $names, $values) = $this->PageModel->convert($pages[$this->session->page]);
    
    /**/
    $this->app->data['inquiry'] = $values;
    $inquiries = $this->InquiryModel->collectByPage($this->user, $this->session, $this->session->page);
    foreach($inquiries as $inquiry){
      if($inquiry->listed){
	if(empty($inquiry->value) === false){
	  $this->app->data['inquiry'][$inquiry->name] = explode(',', $inquiry->value);
	}else{
	  $this->app->data['inquiry'][$inquiry->name] = array();
	}
      }else{
	$this->app->data['inquiry'][$inquiry->name] = $inquiry->value;
      }
    }

    /**/
    $this->app->data['pageProgress'] = $this->PageModel->formatProgress($pages, $this->session->page);
    
    /**/
    return 'work/page';
  }
}
