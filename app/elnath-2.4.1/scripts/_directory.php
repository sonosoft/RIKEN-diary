<?php

/*
 * ディレクトリを作成する
 */
function elnCreateDirectory($base, $root, $path){
  if(count($path) > 0){
    /* ディレクトリを検査 */
    $dirname = implode(DIRECTORY_SEPARATOR, array_merge(array($root, $base), $path));
    if(file_exists($dirname)){
      if(is_dir($dirname) === false){
        throw new Exception(sprintf('"%s" already exists and is not a directory.', $dirname));
      }
      echo sprintf('- directory, "%s" already exists.', $dirname) . PHP_EOL;
      return;
    }

    /* ディレクトリを作成 */
    $dirname = $root . DIRECTORY_SEPARATOR . $base;
    foreach($path as $segment){
      /* パス */
      $dirname .= DIRECTORY_SEPARATOR . $segment;

      /* パスを検査 */
      if(file_exists($dirname)){
        if(is_dir($dirname) === false){
          throw new Exception(sprintf('"%s" already exists and is not a directory.', $dirname));
        }
      }else{
        /* ディレクトリを作成 */
        mkdir($dirname);
        echo sprintf('+ create directory, "%s".', $dirname) . PHP_EOL;
      }

      /**/
      $first = false;
    }
  }
}


/*
 * ディレクトリを削除する
 */
function elnDeleteDirectory($target){
  if(file_exists($target)){
    if(is_dir($target)){
      if(($dir = opendir($target)) !== false){
	while(($name = readdir($dir)) !== false){
	  if(strcmp($name, '.') != 0 && strcmp($name, '..') != 0){
	    elnDeleteDirectory($target . DIRECTORY_SEPARATOR . $name);
	  }
	}
	closedir($dir);
      }
      rmdir($target);
    }else{
      unlink($target);
    }
  }
}


/*
 * ディレクトリおよびファイルをコピーする
 */
function elnCopy($src, $dst){
  if(is_dir($src)){
    /* コピー先ディレクトリを作成 */
    if(file_exists($dst)){
      if(is_dir($dst) === false){
        throw new Exception(sprintf('"%s" already exists and is not a directory.', $dst));
      }
      echo sprintf('- directory, "%s" already exists.', $dst) . PHP_EOL;
    }else{
      mkdir($dst);
      echo sprintf('+ create directory, "%s".', $dst) . PHP_EOL;
    }

    /* ディレクトリ内をコピー */
    if(($dir = opendir($src)) === false){
      throw new Exception(sprintf('failed to open directory, "%s".', $src));
    }
    while(($file = readdir($dir)) !== false){
      if(strlen($file) > 0 && strcmp(substr($file, 0, 1), '.') != 0){
        elnCopy($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
      }
    }
    closedir($dir);
  }else{
    /* ファイルをコピー */
    if(file_exists($dst)){
      if(is_dir($dst)){
        throw new Exception(sprintf('"%s" already exists and is a directory.', $dst));
      }
      echo sprintf('- file, "%s" already exists.', $dst) . PHP_EOL;
    }else{
      global $version;
      /**/
      if(($file = file_get_contents($src)) !== false){
	$file = str_replace('elnath-2.4.1', 'elnath-' . $version, $file);
	file_put_contents($dst, $file);
      }
      if(file_exists($dst) === false){
	throw new Exception(sprintf('failed to create file, "%s".', $dst));
      }
      echo sprintf('+ create file, "%s".', $dst) . PHP_EOL;
    }
  }
}
