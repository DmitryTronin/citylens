#!/usr/bin/env php
<?php
require_once __DIR__ . '/weather.php';

function logMessage(string $message): void { $dir = dirname(__DIR__) . '/logs'; if (!is_dir($dir)) mkdir($dir, 0775, true); error_log('[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, 3, $dir . '/weather.log'); }

$args = $argv;
array_shift($args);
if (($args[0] ?? '') === 'compare') {
    $names = array_slice($args, 1, 3);
    if (count($names) < 2) { fwrite(STDERR, "Usage: weather-cli.php compare <city1> <city2> [city3]\n"); exit(2); }
    $cities = fetchCities($names);
    $labels = ['Temperature' => 'temp', 'Feels-like' => 'feels_like', 'Humidity' => 'humidity', 'Clouds' => 'clouds', 'Precipitation' => 'precipitation', 'Wind' => 'wind'];
    $width = max(array_map(fn($c) => strlen(($c['name'] ?? $c['requested']) . (isset($c['country']) ? ', ' . $c['country'] : '')), $cities));
    $width = max(16, $width);
    printf("%-16s", 'Metric'); foreach ($cities as $c) printf(" | %{$width}s", substr(($c['name'] ?? $c['requested']) . (isset($c['country']) ? ', ' . $c['country'] : ''), 0, $width)); echo PHP_EOL;
    echo str_repeat('-', 17 + count($cities) * ($width + 3)) . PHP_EOL;
    foreach ($labels as $label => $key) { printf("%-16s", $label); foreach ($cities as $c) printf(" | %{$width}s", isset($c['error']) ? 'ERROR' : (string) $c[$key] . ($key === 'temp' || $key === 'feels_like' ? '°C' : ($key === 'wind' ? ' m/s' : '%'))); echo PHP_EOL; }
    printf("%-16s", 'Conditions'); foreach ($cities as $c) printf(" | %{$width}s", isset($c['error']) ? $c['error'] : $c['condition']); echo PHP_EOL;
    echo PHP_EOL . "5-day forecast\n"; foreach ($cities as $c) { echo ($c['name'] ?? $c['requested']) . ": "; echo isset($c['error']) ? $c['error'] : implode(' | ', array_map(fn($d) => date('D', strtotime($d['date'])) . ' ' . $d['temp'] . '°C', $c['forecast'])); echo PHP_EOL; }
    exit(0);
}

$city = trim(implode(' ', $args)) ?: 'Berlin';
try { $c = fetchCity($city); echo "Location: {$c['name']}, {$c['country']}\nCurrent weather: {$c['condition']}\nTemperature: {$c['temp']}°C (feels like {$c['feels_like']}°C)\nHumidity: {$c['humidity']}%\nWind: {$c['wind']} m/s\n"; }
catch (Throwable $e) { logMessage($e->getMessage()); fwrite(STDERR, 'Error: ' . $e->getMessage() . PHP_EOL); exit(1); }
