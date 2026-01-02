<?php
$j = file_get_contents(__DIR__ . '/../resources/menu/verticalMenu.json');
$d = json_decode($j);
if (json_last_error() !== JSON_ERROR_NONE) {
  echo 'ERR: ' . json_last_error_msg() . PHP_EOL;
  exit(1);
}
echo 'OK' . PHP_EOL;
