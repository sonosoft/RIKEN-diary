<?php

$pool = [];
$cnt = 0;
while($cnt < 20){
  $code = sprintf('%04d', 2001 + $cnt);
  if(in_array($code, $pool) === false){
    echo 'INSERT INTO drink (user_id, code, created_at, updated_at) VALUES (0, \''.$code.'\', NOW(), NOW());'.PHP_EOL;
    $pool[] = $code;
    ++ $cnt;
  }
}
