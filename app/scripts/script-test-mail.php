<?php

$text = 'しましたか？【日誌|Q-B-1|2020/10/16】【】です。';

if(preg_match('/【日誌\|([-0-9a-zA-Z]+)\|([\/0-9]+)】/', $text, $matches)){
  var_dump($matches);
}
