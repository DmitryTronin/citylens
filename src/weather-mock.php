<?php

$cityName = "Amsterdam";
$country = "NL";
$currentWeather = "few clouds";
$temp = 19;
$feelsLike = 19;
$humidity = 70;
$windSpeed = 5.66;
$windCondition = "light breeze";

echo "Location: {$cityName}, {$country}" . PHP_EOL;
echo "Current weather: {$currentWeather}" . PHP_EOL;
echo "Temperature: {$temp}°C (feels like {$feelsLike}°C)" . PHP_EOL;
echo "Humidity: {$humidity}%" . PHP_EOL;
echo "Wind: {$windSpeed} m/s - {$windCondition}" . PHP_EOL;
