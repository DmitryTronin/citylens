<?php
require_once dirname(__FILE__) . '/../config.php';
$apiKey = openweathermap_api_key;
$cityId = "2759794";
$googleApiUrl = "https://api.openweathermap.org/data/2.5/weather?id=" . $cityId . "&lang=en&units=metric&APPID=" . $apiKey;

$ch = curl_init();

curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $googleApiUrl);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_VERBOSE, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$response = curl_exec($ch);

if(!$response) {
    die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
}

curl_close($ch);
$data = json_decode($response);

if(!isset($data->weather[0]->description)){
    die('No weather data available');
}

$currentWeather = $data->weather[0]->description;
$feelsLike = round($data->main->feels_like);
$windSpeed = $data->wind->speed;

$windCondition = '';
if ($windSpeed < 1) {
    $windCondition = 'almost no wind';
} elseif($windSpeed > 1 && $windSpeed <= 10) {
    $windCondition = 'light breeze';
} elseif($windSpeed > 10 && $windSpeed <= 20) {
    $windCondition = 'moderate wind';
} elseif($windSpeed > 20 && $windSpeed <= 30) {
    $windCondition = 'strong wind';
} else {
    $windCondition = 'storm outside';
}

echo 'Current weather is: ' . $currentWeather . '. ';
echo 'It feels like ' . $feelsLike . '°C. ';
echo 'There\'s ' . $windCondition . '.';
