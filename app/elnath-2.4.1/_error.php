<?php

echo '<h2>[ERROR!!]</h2>';
echo $e->getMessage() . '<br>';
echo '<br>';
echo debug_print_backtrace();
