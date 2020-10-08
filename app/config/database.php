<?php

/*
 * データベース接続定義
 */
Eln_Database::$databases = array(

  'database'=>array(
    'server'=>'133.242.87.80',
    'username'=>'riken_remote',
    'password'=>'5p922VJSC4zhmwLw',
    'database'=>'riken_diary',
    'charset'=>'utf8',
    'autocommit'=>false,
    'caching'=>false,
    'logging'=>true,
  ),
  
  'debug'=>array(
    'server'=>'localhost',
    'username'=>'riken',
    'password'=>'K2TJHUbteWez6Aph',
    'database'=>'riken_diary',
    'charset'=>'utf8',
    'autocommit'=>false,
    'caching'=>false,
    'logging'=>true,
  ),

);
