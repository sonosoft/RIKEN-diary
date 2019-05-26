<?php

/*
 * データベース接続定義
 */
Eln_Database::$databases = array(

  'debug'=>array(
    'server'=>'localhost',
    'username'=>'user',
    'password'=>'password',
    'database'=>'${appname}',
    'charset'=>'utf8',
    'table_prefix'=>'${appname}_',
    'autocommit'=>false,
    'caching'=>false,
    'logging'=>true,
  ),

  'release'=>array(
    'server'=>'localhost',
    'username'=>'user',
    'password'=>'password',
    'database'=>'${appname}',
    'charset'=>'utf8',
    'table_prefix'=>'${appname}_',
    'autocommit'=>false,
  ),

);
