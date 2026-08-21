<?php

declare(strict_types=1);

/** Shared weather fetching and formatting helpers for the web UI and CLI. */
function weatherConfig(): void
{
    $configPath = dirname(__DIR__) . '/config.php';
    if (!file_exists($configPath)) {
        throw new RuntimeException('config.php file is missing.');
    }
    require_once $configPath;
    if (!defined('openweathermap_api_key') || !openweathermap_api_key) {
        throw new RuntimeException('OpenWeatherMap API key is not configured.');
    }
}

function weatherRequest(string $url): array
{
    $ch = curl_init($url);
    if (!$ch) throw new RuntimeException('Failed to initialize cURL.');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_TIMEOUT => 15]);
    $response = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    if ($response === false) throw new RuntimeException('Weather request failed: ' . $error);
    $data = json_decode($response, true);
    if (!is_array($data) || $status >= 400 || isset($data['cod']) && (int) $data['cod'] >= 400) {
        throw new RuntimeException((string) ($data['message'] ?? 'No weather data available.'));
    }
    return $data;
}

function weatherIcon(int $id): string
{
    return match (true) {
        $id >= 200 && $id < 300 => 'THUNDER', $id >= 300 && $id < 400 => 'SLEET',
        $id >= 500 && $id < 600 => $id === 511 ? 'SNOW' : 'RAIN',
        $id >= 600 && $id < 700 => 'SNOW', $id === 741 => 'FOG', $id >= 700 && $id < 800 => 'FOG',
        $id === 800 => 'CLEAR_DAY', $id <= 802 => 'PARTLY_CLOUDY_DAY', default => 'CLOUDY',
    };
}

function fetchCity(string $name): array
{
    weatherConfig();
    $name = trim($name);
    if ($name === '') throw new RuntimeException('City name is empty.');
    $cacheDir = dirname(__DIR__) . '/data';
    $cacheFile = $cacheDir . '/weather-' . hash('sha256', strtolower($name)) . '.json';
    if (is_file($cacheFile) && filemtime($cacheFile) >= time() - 600) {
        $cached = json_decode((string) file_get_contents($cacheFile), true);
        if (is_array($cached)) return $cached;
    }
    $key = urlencode((string) openweathermap_api_key);
    $query = urlencode($name);
    $base = 'https://api.openweathermap.org/data/2.5/';
    $current = weatherRequest($base . 'weather?q=' . $query . '&lang=en&units=metric&APPID=' . $key);
    $forecast = weatherRequest($base . 'forecast?q=' . $query . '&lang=en&units=metric&APPID=' . $key);
    $days = [];
    foreach ($forecast['list'] ?? [] as $item) {
        $date = date('Y-m-d', (int) $item['dt']);
        if (!isset($days[$date]) && count($days) < 5) $days[$date] = ['date' => $date, 'temp' => round((float) $item['main']['temp']), 'description' => (string) $item['weather'][0]['description'], 'icon' => weatherIcon((int) $item['weather'][0]['id']), 'precipitation' => round(((float) ($item['pop'] ?? 0)) * 100), 'clouds' => (int) ($item['clouds']['all'] ?? 0)];
    }
    $result = ['requested' => $name, 'name' => (string) $current['name'], 'country' => (string) ($current['sys']['country'] ?? ''), 'condition' => (string) $current['weather'][0]['description'], 'icon' => weatherIcon((int) $current['weather'][0]['id']), 'temp' => round((float) $current['main']['temp']), 'feels_like' => round((float) $current['main']['feels_like']), 'humidity' => (int) $current['main']['humidity'], 'wind' => (float) ($current['wind']['speed'] ?? 0), 'clouds' => (int) ($current['clouds']['all'] ?? 0), 'precipitation' => round(((float) ($forecast['list'][0]['pop'] ?? 0)) * 100), 'forecast' => array_values($days)];
    if (!is_dir($cacheDir)) mkdir($cacheDir, 0775, true);
    file_put_contents($cacheFile, json_encode($result));
    return $result;
}

function fetchCities(array $names): array
{
    $results = [];
    foreach (array_slice($names, 0, 3) as $name) {
        try { $results[] = fetchCity((string) $name); }
        catch (Throwable $e) { $results[] = ['requested' => trim((string) $name), 'error' => $e->getMessage()]; }
    }
    return $results;
}

