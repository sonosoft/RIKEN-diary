<?php

/*
 * URIルート定義
 */
Eln_Router::$routes = array(
			    
  'diary'=>array(
    'uri'=>'/:token/:code',
    'defaults'=>array(
      'controller'=>'work',
      'action'=>'index',
      'token'=>null,
      'code'=>null,
    ),
    'patterns'=>array(
      'token'=>'/^[0-9a-zA-z]{15}$/',
      'code'=>'/^[0-9]{3}$/',
    ),
  ),

  'restoration'=>array(
    'uri'=>'/r2/:token',
    'defaults'=>array(
      'controller'=>'work',
      'action'=>'restore',
      'token'=>null,
    ),
    'patterns'=>array(
      'token'=>'/^[0-9a-zA-z]{10}$/',
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
