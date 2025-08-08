#!/usr/bin/env php
<?php
require_once dirname(__FILE__) . '/../config.php';

function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] $message" . PHP_EOL, 3, __DIR__ . '/../logs/weather.log');
}

$apiKey = openweathermap_api_key;
$cityId = "2759794";
$googleApiUrl = "https://api.openweathermap.org/data/2.5/weather?id=" . $cityId . "&lang=en&units=metric&APPID=" . $apiKey;

logMessage("Starting weather fetch for city ID: $cityId");

$ch = curl_init();

curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $googleApiUrl);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_VERBOSE, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$response = curl_exec($ch);

if (!$response) {
    $error = "cURL error: \"" . curl_error($ch) . "\" - Code: " . curl_errno($ch);
    logMessage($error);
    echo "Error: \"" . curl_error($ch) . "\" - Code: " . curl_errno($ch) . PHP_EOL;
    exit(1);
}

logMessage("Successfully received API response");

curl_close($ch);
$data = json_decode($response);

if (!isset($data->weather[0]->description)) {
    logMessage("Error: No weather data available in API response");
    echo "No weather data available" . PHP_EOL;
    exit(1);
}

logMessage("Successfully parsed weather data");

$currentWeather = $data->weather[0]->description;
$feelsLike = round($data->main->feels_like);
$windSpeed = $data->wind->speed;
$cityName = $data->name;
$country = $data->sys->country;
$temp = round($data->main->temp);
$humidity = $data->main->humidity;

$windCondition = match (true) {
    $windSpeed < 1 => 'almost no wind',
    $windSpeed <= 10 => 'light breeze',
    $windSpeed <= 20 => 'moderate wind',
    $windSpeed <= 30 => 'strong wind',
    default => 'storm outside',
};

logMessage("Displaying weather for {$cityName}, {$country}: {$currentWeather}, {$temp}°C");

echo "Location: {$cityName}, {$country}" . PHP_EOL;
echo "Current weather: {$currentWeather}" . PHP_EOL;
echo "Temperature: {$temp}°C (feels like {$feelsLike}°C)" . PHP_EOL;
echo "Humidity: {$humidity}%" . PHP_EOL;
echo "Wind: {$windSpeed} m/s - {$windCondition}" . PHP_EOL;

flush();
