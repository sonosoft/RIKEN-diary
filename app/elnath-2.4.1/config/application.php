<?php

Eln_Application::$name = '${appname}';
Eln_Application::$mode = 'debug';
Eln_Application::$settings = array(
  
  'debug'=>array(
    'locale'=>'ja_JP.UTF-8',
    'timezone'=>'Asia/Tokyo',
    'memory_limit'=>-1,
    'time_limit'=>0,
    'display_error'=>true,
    'session_name'=>'${ssname}',
  ),
  
  'release'=>array(
    'locale'=>'ja_JP.UTF-8',
    'timezone'=>'Asia/Tokyo',
    'memory_limit'=>-1,
    'time_limit'=>0,
    'session_name'=>'${ssname}',
  ),

);

/* ===== ===== */

/*
 * アプリケーション定数の定義
 */
define('STATUS_ACTIVE', 1);
define('STATUS_INACTIVE', 2);
