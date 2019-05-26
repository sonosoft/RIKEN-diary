<?php

/*
 * URIルート定義
 */
Eln_Router::$routes = array(
			    
  'default'=>array(
    'uri'=>'/:controller/:action/:id',
    'defaults'=>array(
      'controller'=>'home',
      'action'=>'index',
      'id'=>null
    ),
    'patterns'=>array(
      'id'=>'int'
    ),
  ),

  'download'=>array(
    'uri'=>'/:application/:controller',
    'defaults'=>array('action'=>'index'),
    'patterns'=>array('application'=>'/^download[0-9]+$/'),
  ),
			    
  'blog'=>array(
    'uri'=>'/blog/:user/:year/:month/:day',
    'defaults'=>array(
      'controller'=>'blog',
      'action'=>'index'
    ),
    'patterns'=>array(
      'user'=>'/^u[0-9]+$/',
      'year'=>'int',
      'month'=>'int',
      'day'=>'integer'
    ),
  ),

);

/*
 * セッション引き継ぎ
 */
Eln_Router::$session = Eln_Router::SESSION_AUTO;
