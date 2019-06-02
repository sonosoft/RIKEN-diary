<?php

/*
 * URIルート定義
 */
Eln_Router::$routes = array(
			    
  'diary'=>array(
    'uri'=>'/:token',
    'defaults'=>array(
      'controller'=>'work',
      'action'=>'index',
      'token'=>null,
    ),
    'patterns'=>array(
      'token'=>'/^[0-9a-zA-z]{15}$/',
    ),
  ),

  'default'=>array(
    'uri'=>'/:controller/:action/:id',
    'defaults'=>array(
      'controller'=>'page',
      'action'=>'index',
      'id'=>null
    ),
    'patterns'=>array(
      'id'=>'int'
    ),
  ),

);

/*
 * セッション引き継ぎ
 */
Eln_Router::$session = Eln_Router::SESSION_COOKIE;
