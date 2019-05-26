<?php

/**/
define('ELN_SCRIPT_ROOT', __DIR__);
define('ELN_VERSION_ROOT', dirname(ELN_SCRIPT_ROOT));
define('ELN_PROJECT_ROOT', dirname(ELN_VERSION_ROOT));
set_include_path(
		 ELN_PROJECT_ROOT
		 .
		 PATH_SEPARATOR
		 .
		 ELN_VERSION_ROOT
		 .
		 PATH_SEPARATOR
		 .
		 get_include_path()
		 );

/**/
function eln_camelize($str, $heading=true){
  if($heading){
    $str = preg_replace_callback(
      '/^([a-z])/',
      function($matches){
	return strtoupper($matches[1]);
      },
      $str
    );
  }
  return preg_replace_callback(
    '/_([a-z])/',
    function($matches){
      return strtoupper($matches[1]);
    },
    $str
  );
}
