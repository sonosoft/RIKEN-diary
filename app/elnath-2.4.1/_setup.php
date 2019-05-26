<?php

$appDir = __DIR__.DIRECTORY_SEPARATOR.'app';
$varDir = $appDir.DIRECTORY_SEPARATOR.'var';
if(file_exists($varDir) === false){
  mkdir($varDir);
}

foreach(array('log', 'tmp', 'cache') as $dir){
  $path = $varDir.DIRECTORY_SEPARATOR.$dir;
  if(file_exists($path) === false){
    mkdir($path);
  }
}

$cacheDir = $varDir.DIRECTORY_SEPARATOR.'cache';
foreach(array('models', 'views') as $dir){
  $path = $cacheDir.DIRECTORY_SEPARATOR.$dir;
  if(file_exists($path) === false){
    mkdir($path);
  }
}

echo 'Setting up server ... OK';
