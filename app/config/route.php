<?php

/*
 * URIルート定義
 */
Eln_Router::$routes = array(
			    
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
Eln_Router::$session = Eln_Router::SESSION_AUTO;
