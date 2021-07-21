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
  $administratorModel = getModel('Administrator');
  $password = $administratorModel->encrypt('riken19##');

  /* administrator, session */
  echo 'CREATE TABLE administrator;'.PHP_EOL;
  $db->query(
    'CREATE TABLE IF NOT EXISTS `administrator` ('.
    ' `id` int NOT NULL AUTO_INCREMENT,'.
    ' `password` varchar(40) NOT NULL,'.
    ' `created_at` datetime NOT NULL,'.
    ' `updated_at` datetime NOT NULL,'.
    ' PRIMARY KEY (`id`) '.
    ') ENGINE=InnoDB'
  );
  $db->query('TRUNCATE `administrator`');
  $db->query(
    'INSERT INTO `administrator` (`password`, `created_at`, `updated_at`) '.
    'VALUES ("'.$password.'", NOW(), NOW())'
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
    ') ENGINE=InnoDB'
  );

  /* project */
  echo 'CREATE TABLE project;'.PHP_EOL;
  $db->query(
    'CREATE TABLE IF NOT EXISTS `project` ('.
    ' `id` int NOT NULL AUTO_INCREMENT,'.
    ' `token` varchar(10) NOT NULL,'.
    ' `title` varchar(100) NOT NULL,'.
    ' `from_date` date NOT NULL,'.
    ' `to_date` date NOT NULL,'.
    ' `status` tinyint(1) NOT NULL,'.
    ' `created_at` datetime NOT NULL,'.
    ' `updated_at` datetime NOT NULL,'.
    ' `deleted_at` datetime DEFAULT NULL,'.
    ' PRIMARY KEY (`id`),'.
    ' UNIQUE KEY `token` (`token`)'.
    ') ENGINE=InnoDB'
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
    ') ENGINE=InnoDB'
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
    ') ENGINE=InnoDB'
  );
  echo 'CREATE TABLE project_user;'.PHP_EOL;
  $db->query(
    'CREATE TABLE IF NOT EXISTS `project_user` ('.
    ' `id` int NOT NULL AUTO_INCREMENT,'.
    ' `project_id` int NOT NULL,'.
    ' `user_id` int NOT NULL,'.
    ' PRIMARY KEY (`id`),'.
    ' INDEX `project_id` (`project_id`),'.
    ' INDEX `user_id` (`user_id`)'.
    ') ENGINE=InnoDB'
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
    ') ENGINE=InnoDB'
  );

  /* diary, kokoroscale */
  echo 'CREATE TABLE diary;'.PHP_EOL;
  $db->query(
    'CREATE TABLE IF NOT EXISTS `diary` ('.
    ' `id` int NOT NULL AUTO_INCREMENT,'.
    ' `code` varchar(5) NOT NULL,'.
    ' `title` varchar(100) NOT NULL,'.
    ' `overview` text DEFAULT NULL,'.
    ' `from_time` smallint NOT NULL,'.
    ' `to_time` smallint NOT NULL,'.
    ' `separated` tinyint(1) NOT NULL,'.
    ' `status` tinyint(1) NOT NULL,'.
    ' `created_at` datetime NOT NULL,'.
    ' `updated_at` datetime NOT NULL,'.
    ' `deleted_at` datetime DEFAULT NULL,'.
    ' PRIMARY KEY (`id`),'.
    ' INDEX `code` (`code`)'.
    ') ENGINE=InnoDB'
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
    ' `email_alt` varchar(200) DEFAULT NULL,'.
    ' `sex` tinyint(1) NOT NULL,'.
    ' `birthday` date NOT NULL,'.
    ' `status` tinyint(1) NOT NULL,'.
    ' `created_at` datetime NOT NULL,'.
    ' `updated_at` datetime NOT NULL,'.
    ' `deleted_at` datetime DEFAULT NULL,'.
    ' PRIMARY KEY (`id`),'.
    ' INDEX `code` (`code`),'.
    ' UNIQUE KEY `token` (`token`)'.
    ') ENGINE=InnoDB'
  );

  /* visit, answer */
  echo 'CREATE TABLE visit;'.PHP_EOL;
  $db->query(
    'CREATE TABLE IF NOT EXISTS `visit` ('.
    ' `id` int NOT NULL AUTO_INCREMENT,'.
    ' `user_id` int NOT NULL,'.
    ' `project_id` int NOT NULL,'.
    ' `diary_id` int DEFAULT NULL,'.
    ' `diaries` text DEFAULT NULL,'.
    ' `page` tinyint(2) NOT NULL,'.
    ' `status` tinyint(1) NOT NULL,'.
    ' `started_at` datetime NOT NULL,'.
    ' `accessed_at` datetime DEFAULT NULL,'.
    ' `finished_at` datetime DEFAULT NULL,'.
    ' PRIMARY KEY (`id`),'.
    ' INDEX `project_id` (`project_id`),'.
    ' INDEX `user_id` (`user_id`)'.
    ') ENGINE=InnoDB'
  );
  echo 'CREATE TABLE answer;'.PHP_EOL;
  $db->query(
    'CREATE TABLE IF NOT EXISTS `answer` ('.
    ' `id` int NOT NULL AUTO_INCREMENT,'.
    ' `user_id` int NOT NULL,'.
    ' `visit_id` int NOT NULL,'.
    ' `page` tinyint(2) NOT NULL,'.
    ' `name` varchar(50) NOT NULL,'.
    ' `header` varchar(50) NOT NULL,'.
    ' `value` varchar(100) DEFAULT NULL,'.
    ' `listed` tinyint(1) DEFAULT NULL,'.
    ' `answered_at` datetime NOT NULL,'.
    ' PRIMARY KEY (`id`),'.
    ' INDEX `user_id` (`user_id`),'.
    ' INDEX `visit_id` (`visit_id`)'.
    ') ENGINE=InnoDB'
  );
  
  /* locker */
  echo 'CREATE TABLE locker;'.PHP_EOL;
  $db->query(
    'CREATE TABLE IF NOT EXISTS `locker` ('.
    ' `id` int NOT NULL,'.
    ' PRIMARY KEY (`id`)'.
    ') ENGINE=InnoDB'
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
