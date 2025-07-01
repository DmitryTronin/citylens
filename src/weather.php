<?php
$configPath = dirname(__FILE__) . '/../config.php';

if (!file_exists($configPath)) {
    die(
        '<b>Error:</b> <code>config.php</code> file is missing.<br>' .
        'Please create it and add your OpenWeatherMap API key.'
    );
}

require_once $configPath;

if (!defined('openweathermap_api_key') || !openweathermap_api_key) {
    die(
        '<b>Error:</b> OpenWeatherMap API key is not defined in <code>config.php</code>.<br>' .
        'Add: <code>define("openweathermap_api_key", "your-api-key");</code>'
    );
}

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

if (!$response) {
    die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
}

curl_close($ch);
$data = json_decode($response);

if (!isset($data->weather[0]->description)) {
    die('No weather data available');
}


$currentWeather = $data->weather[0]->description;
$weatherId = $data->weather[0]->id;

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

echo "<p><strong>Location:</strong> {$cityName}, {$country}</p>";
echo "<p><strong>Current weather:</strong> {$currentWeather} <span data-weather-icon=\"{$weatherIcon}\"></span></p>";
echo "<p><strong>Temperature:</strong> {$temp}°C (feels like {$feelsLike}°C)</p>";
echo "<p><strong>Humidity:</strong> {$humidity}%</p>";
echo "<p><strong>Wind:</strong> {$windSpeed} m/s - {$windCondition}</p>";

