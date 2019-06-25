<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * page_model.php
 */


class PageModelFactory extends Eln_Object {

  public function load($diaries){
    $pages = array();
    $index = 1;
    foreach($diaries as $diary){
      if(file_exists($diary->path)){
	$xml = simplexml_load_file($diary->path);
	foreach($xml->page as $page){
	  $pages[$index] = $page;
	  ++ $index;
	}
      }
    }
    return $pages;
  }

  public function getScale($page){
    if(isset($page['type']) && strcmp($page['type'], 'scale') == 0){
      $scale = array(
	'name'=>strval($page['name']),
	'header'=>strval($page['header']),
	'title'=>'',
	'xpos'=>'',
	'xneg'=>'',
	'ypos'=>'',
	'yneg'=>'',
      );
      foreach($page->title as $title){
	$scale['title'] = $title;
      }
      foreach($page->label as $label){
	if(isset($label['position'])){
	  if(strcmp($label['position'], 'x-positive') == 0){
	    $scale['xpos'] = strval($label);
	  }else if(strcmp($label['position'], 'x-negative') == 0){
	    $scale['xneg'] = strval($label);
	  }else if(strcmp($label['position'], 'y-positive') == 0){
	    $scale['ypos'] = strval($label);
	  }else if(strcmp($label['position'], 'y-negative') == 0){
	    $scale['yneg'] = strval($label);
	  }
	}
      }
      foreach($page->option as $option){
	if(isset($option['type'])){
	  $scale[strval($option['type'])] = true;
	}
      }
      return $scale;
    }
    return false;
  }
  
  public function convert($page){
    $rows = array();
    $names = array();
    $values = array();
    foreach($page->row as $line){
      $row = array('items'=>array());
      if(isset($line['type'])){
	$row['type'] = strval($line['type']);
      }else{
	$row['type'] = 'row';
      }
      foreach($line->item as $item){
	$inline = array();
	$choices = array();
	if(strcmp($item['type'], 'inline') == 0){
	  foreach($item->item as $block){
	    $inline[] = $this->extract($block);
	    if(isset($block['header'])){
	      $hidden = isset($block['hidden']) ? true : false;
	      $names[] = array(strval($block['header']), strval($block['name']), $hidden);
	    }
	    if(isset($block['name']) && ord($block['name']) != ord('_')){
	      $values[strval($block['name'])] = $this->getValue($block);
	    }
	  }
	}else{
	  if(isset($item['header'])){
	    $hidden = isset($item['hidden']) ? true : false;
	    $names[] = array(strval($item['header']), strval($item['name']), $hidden);
	  }
	  if(isset($item['name']) && ord($item['name']) != ord('_')){
	    $values[strval($item['name'])] = $this->getValue($item);
	  }
	  if(strcmp($item['type'], 'choice') == 0){
	    foreach($item->choice as $block){
	      $choices[] = $this->extract($block);
	    }
	  }
	}
	$array = $this->extract($item);
	if(isset($array['width']) === false){
	  $array['width'] = 12;
	}
	if(empty($inline) === false){
	  $array['inline'] = $inline;
	}
	if(empty($choices) === false){
	  $array['choices'] = $choices;
	}
	$row['items'][] = $array;
      }
      $rows[] = $row;
    }
    return array($rows, $names, $values);
  }

  public function collectIndexes($pages){
    $indexes = array();
    foreach($pages as $index=>$page){
      $indexes[] = $index;
    }
    return $indexes;
  }

  /* ===== ===== */

  private function extract($item){
    $src = json_decode(json_encode($item), true);
    $data = $src['@attributes'];
    $data['text'] = strval($item);
    $ex = array();
    if(empty($data['enableIf']) === false){
      $ex[] = 'related';
    }
    if(isset($data['optional']) && intval($data['optional'])){
      $ex[] = 'optional';
    }
    if(empty($ex) === false){
      $data['ex'] = implode(' ', $ex);
    }
    return $data;
  }

  private function getValue($data){
    if(in_array($data['type'], array('text', 'number', 'date'))){
      return strval($data);
    }
    return null;
  }
}
