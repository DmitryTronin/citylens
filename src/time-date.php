<?php

require_once 'vendor/autoload.php';

$date = new DateTime('now', new DateTimeZone('Europe/Amsterdam'));
echo "Time in Amsterdam: " . $date->format('Y-m-d H:i:s');

$date->setTimeZone(new DateTimeZone('Europe/Moscow'));
echo "\nTime in Moscow: " . $date->format('Y-m-d H:i:s') . PHP_EOL;
