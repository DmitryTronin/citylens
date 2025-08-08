<?php

declare(strict_types=1);

$configPath = dirname(__FILE__) . '/../config.php';

if (!file_exists($configPath)) {
    $errorMsg = 'Error: config.php file is missing. Please create it and add your OpenWeatherMap API key.';
    error_log($errorMsg);
    die(
        '<b>Error:</b> <code>config.php</code> file is missing.<br>' .
        'Please create it and add your OpenWeatherMap API key.'
    );
}

require_once $configPath;

if (!defined('openweathermap_api_key') || !openweathermap_api_key) {
    $errorMsg = 'Error: OpenWeatherMap API key is not defined in config.php. Add: define("openweathermap_api_key", "your-api-key");';
    error_log($errorMsg);
    die(
        '<b>Error:</b> OpenWeatherMap API key is not defined in <code>config.php</code>.<br>' .
        'Add: <code>define("openweathermap_api_key", "your-api-key");</code>'
    );
}

$apiKey = (string) openweathermap_api_key;
$cityId = '2759794';
$openWeatherApiUrl = 'https://api.openweathermap.org/data/2.5/weather?id=' . urlencode($cityId) . '&lang=en&units=metric&APPID=' . urlencode($apiKey);

$ch = curl_init();

if (!$ch) {
    $errorMsg = 'Error: Failed to initialize cURL';
    error_log($errorMsg);
    die($errorMsg);
}

curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $openWeatherApiUrl);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_VERBOSE, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$response = curl_exec($ch);

if ($response === false) {
    $errorMsg = 'Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch);
    error_log($errorMsg);
    die($errorMsg);
}

curl_close($ch);
$data = json_decode($response, false, 512, JSON_THROW_ON_ERROR);


if (!isset($data->weather[0]->description)) {
    $errorMsg = 'No weather data available';
    error_log($errorMsg);
    die($errorMsg);
}

$currentWeather = (string) $data->weather[0]->description;
$weatherId = (int) $data->weather[0]->id;

$weatherIcon = match (true) {
    $weatherId >= 200 && $weatherId < 300 => 'THUNDER',
    $weatherId >= 300 && $weatherId < 400 => 'SLEET',
    $weatherId >= 500 && $weatherId < 600 => ($weatherId == 511 ? 'SNOW' : 'RAIN'),
    $weatherId >= 600 && $weatherId < 700 => match (true) {
        $weatherId == 611 || $weatherId == 612 || $weatherId == 613 => 'SLEET',
        default => 'SNOW',
    },
    $weatherId >= 700 && $weatherId < 800 => match ($weatherId) {
        741 => 'FOG',
        781 => 'WIND',
        default => 'FOG',
    },
    $weatherId == 800 => 'CLEAR_DAY',
    $weatherId == 801 => 'PARTLY_CLOUDY_DAY',
    $weatherId == 802 => 'PARTLY_CLOUDY_DAY',
    $weatherId == 803, $weatherId == 804 => 'CLOUDY',
    default => 'PARTLY_CLOUDY_DAY',
};
$feelsLike = (int) round($data->main->feels_like);
$windSpeed = (float) $data->wind->speed;
$cityName = (string) $data->name;
$country = (string) $data->sys->country;
$temp = (int) round($data->main->temp);
$humidity = (int) $data->main->humidity;

$windCondition = match (true) {
    $windSpeed < 1 => 'almost no wind',
    $windSpeed <= 10 => 'light breeze',
    $windSpeed <= 20 => 'moderate wind',
    $windSpeed <= 30 => 'strong wind',
    default => 'storm outside',
};

echo "<p><strong>Location:</strong> {$cityName}, {$country}</p>";
echo "<p><strong>Current weather:</strong> {$currentWeather} <span data-weather-icon=\"{$weatherIcon}\"></span></p>";
echo "<p><strong>Temperature:</strong> {$temp}°C (feels like {$feelsLike}°C)</p>";
echo "<p><strong>Humidity:</strong> {$humidity}%</p>";
echo "<p><strong>Wind:</strong> {$windSpeed} m/s - {$windCondition}</p>";

