<?php

Eln_Application::$name = 'dialy';
Eln_Application::$mode = 'debug';
Eln_Application::$settings = array(
  
  'debug'=>array(
    'locale'=>'ja_JP.UTF-8',
    'timezone'=>'Asia/Tokyo',
    'memory_limit'=>-1,
    'time_limit'=>0,
    'display_error'=>true,
    'session_name'=>'96207a573a0f9d637fcb538ab9d7eabf145c3ff6',
  ),
  
  'release'=>array(
    'locale'=>'ja_JP.UTF-8',
    'timezone'=>'Asia/Tokyo',
    'memory_limit'=>-1,
    'time_limit'=>0,
    'session_name'=>'96207a573a0f9d637fcb538ab9d7eabf145c3ff6',
  ),

);

/* ===== ===== */

/*
 * ステータス
 */
define('STATUS_ENABLED', 1);
define('STATUS_DISABLED', 0);
/**/
define('MAIL_BEFORE', 1);
define('MAIL_AFTER', 2);
define('MAIL_DURING', 3);
define('MAIL_DATE', 4);

/*
 * メール設定
 */
define('MAIL_HOST', 'postman.riken.jp');
define('MAIL_ENCRPT', 'ssl');
define('SMTP_PORT', 465);
define('MAIL_FROM', 'rch_he@ml.riken.jp');
define('MAIL_FROM_NAME', '理化学研究所 リサーチコンプレックス戦略室');
define('MAIL_ENCODING', 'base64');
define('MAIL_CHARSET', 'UTF-8');
