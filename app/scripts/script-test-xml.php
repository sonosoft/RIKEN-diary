<?php

/**/
$xml = simplexml_load_file($argv[1]);
if($xml !== false){
  $index = 1;
  foreach($xml->page as $page){
    if($index == 2){
      echo $page['type'].PHP_EOL;
      foreach($page->title as $title){
	echo 'Title: '.$title.PHP_EOL;
      }
      foreach($page->label as $label){
	echo $label['position'].':'.strval($label).PHP_EOL;
      }
    }
    ++ $index;
  }
}
