<?php

/**/
include_once(__DIR__.'/script-lib.inc');

/* アプリケーション */
$app = getApp();

/**/
try{
  /* 現在日時 */
  $now = Eln_Date::now();

  /* データベース接続 */
  $db = Eln_Database::setCurrentDatabase('database');
  $db->open();
  $db->begin();

  /* モデル */
  $testModel = getModel('Test');

  /* administrator, session */
  echo 'CREATE TABLE administrator;'.PHP_EOL;
  $db->query(
    'CREATE TABLE IF NOT EXESTS `administrator` ('.
    ' `id` int NOT NULL AUTO_INCREMENT,'.
    ' `password` varchar(40) NOT NULL,'.
    ' `created_at` datetime NOT NULL,'.
    ' `updated_at` datetime NOT NULL,'.
    ' PRIMARY KEY (`id`) '.
    ')'
  );
  echo 'CREATE TABLE session;'.PHP_EOL;
  $db->query(
    'CREATE TABLE IF NOT EXISTS `session` ('.
    ' `id` int NOT NULL AUTO_INCREMENT,'.
    ' `administrator_id` int NOT NULL,'.
    ' `token` varchar(40) NOT NULL,'.
    ' `created_at` datetime NOT NULL,'.
    ' `updated_at` datetime NOT NULL,'.
    ' PRIMARY KEY (`id`),'.
    ' UNIQUE KEY `token` (`token`)'.
    ')'
  );

  /* project */
  echo 'CREATE TABLE project;'.PHP_EOL;
  $db->query(
    'CREATE TABLE IF NOT EXISTS `project` ('.
    ' `id` int NOT NULL AUTO_INCREMENT,'.
    ' `title` varchar(100) NOT NULL,'.
    ' `overview` varchar(400) NOT NULL,'.
    ' `started_on` date NOT NULL,'.
    ' `ended_on` date NOT NULL,'.
    ' `status` tinyint(1) NOT NULL,'.
    ' `created_at` datetime NOT NULL,'.
    ' `updated_at` datetime NOT NULL,'.
    ' `deleted_at` datetime DEFAULT NULL,'.
    ' PRIMARY KEY (`id`)'.
    ')'
  );
  echo 'CREATE TABLE project_diary;'.PHP_EOL;
  $db->query(
    'CREATE TABLE IF NOT EXISTS `project_diary` ('.
    ' `id` int NOT NULL AUTO_INCREMENT,'.
    ' `project_id` int NOT NULL,'.
    ' `diary_id` int NOT NULL,'.
    ' PRIMARY KEY (`id`),'.
    ' INDEX `project_id` (`project_id`),'.
    ' INDEX `diary_id` (`diary_id`)'.
    ')'
  );
  echo 'CREATE TABLE project_mail;'.PHP_EOL;
  $db->query(
    'CREATE TABLE IF NOT EXISTS `project_mail` ('.
    ' `id` int NOT NULL AUTO_INCREMENT,'.
    ' `project_id` int NOT NULL,'.
    ' `mail_id` int NOT NULL,'.
    ' PRIMARY KEY (`id`),'.
    ' INDEX `project_id` (`project_id`),'.
    ' INDEX `mail_id` (`mail_id`)'.
    ')'
  );
  
  /* user */
  echo 'CREATE TABLE user;'.PHP_EOL;
  $db->query(
    'CREATE TABLE IF NOT EXISTS `user` ('.
    ' `id` int NOT NULL AUTO_INCREMENT,'.
    ' `code` varchar(8) NOT NULL,'.
    ' `token` varchar(10) NOT NULL,'.
    ' `family_name` varchar(100) NOT NULL,'.
    ' `first_name` varchar(100) NOT NULL,'.
    ' `kana` varchar(100) NOT NULL,'.
    ' `email` varchar(200) NOT NULL,'.
    ' `sex` tinyint(1) NOT NULL,'.
    ' `birthday` date NOT NULL,'.
    ' `tel` varchar(20) NOT NULL,'.
    ' `postal_code` varchar(10) NOT NULL,'.
    ' `postal_address` varchar(200) NOT NULL,'.
    ' `status` tinyint(1) NOT NULL,'.
    ' `created_at` datetime NOT NULL,'.
    ' `updated_at` datetime NOT NULL,'.
    ' `deleted_at` datetime DEFAULT NULL,'.
    ' PRIMARY KEY (`id`),'.
    ' INDEX `code` (`code`),'.
    ' UNIQUE KEY `token` (`token`)'.
    ')'
  );

  /* mail */
  echo 'CREATE TABLE mail;'.PHP_EOL;
  $db->query(
    'CREATE TABLE IF NOT EXISTS `mail` ('.
    ' `id` int NOT NULL AUTO_INCREMENT,'.
    ' `code` varchar(5) NOT NULL,'.
    ' `title` varchar(200) NOT NULL,'.
    ' `body` text NOT NULL,'.
    ' `schedule` text NOT NULL,'.
    ' `status` tinyint(1) NOT NULL,'.
    ' `created_at` datetime NOT NULL,'.
    ' `updated_at` datetime NOT NULL,'.
    ' `deleted_at` datetime DEFAULT NULL,'.
    ' PRIMARY KEY (`id`),'.
    ' INDEX `code` (`code`)'.
    ')'
  );

  /* diary, kokoroscale */
  echo 'CREATE TABLE diary;'.PHP_EOL;
  $db->query(
    'CREATE TABLE IF NOT EXISTS `diary` ('.
    ' `id` int NOT NULL AUTO_INCREMENT,'.
    ' `code` varchar(5) NOT NULL,'.
    ' `title` varchar(100) NOT NULL,'.
    ' `overview` text NOT NULL,'.
    ' `from_time` smallint NOT NULL,'.
    ' `to_time` smallint NOT NULL,'.
    ' `status` tinyint(1) NOT NULL,'.
    ' `created_at` datetime NOT NULL,'.
    ' `updated_at` datetime NOT NULL,'.
    ' `deleted_at` datetime DEFAULT NULL,'.
    ' PRIMARY KEY (`id`),'.
    ' INDEX `code` (`code`)'.
    ')'
  );

  /* locker */
  echo 'CREATE TABLE locker;'.PHP_EOL;
  $db->query(
    'CREATE TABLE IF NOT EXISTS `locker` ('.
    ' `id` int NOT NULL,'.
    ' PRIMARY KEY (`id`)'.
    ')'
  );

  /* コミット */
  $db->commit();
}catch(Exception $e){
  /* ロールバック */
  $db->rollback();

  /* エラー */
  echo '***** ERROR ***** ['.date('Y/m/d H:i:s').'] *****'.PHP_EOL;
  echo $e->getMessage().PHP_EOL;
  echo '----- ----- ----- ----- -----'.PHP_EOL;
  echo debug_print_backtrace();
  echo '----- ----- ----- ----- -----'.PHP_EOL;
  echo PHP_EOL;
}
