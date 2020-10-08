<?php

if(function_exists('bindtextdomain') === false){
  function bindtextdomain($domain, $directory)
  {
  }
}
if(function_exists('textdomain') === false){
  function textdomain($domain)
  {
  }
}
if(function_exists('_') === false){
  function _($str)
  {
    return $str;
  }
}

$file = __DIR__.$_SERVER['SCRIPT_NAME'];
if(file_exists($file) && is_file($file)){
  return false;
}

include(__DIR__.'/index.php');
