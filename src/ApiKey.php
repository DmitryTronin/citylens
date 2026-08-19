<?php
declare(strict_types=1);
namespace CityLens;
use CityLens\Exception\WeatherException;
final class ApiKey
{
    public static function load(string $projectRoot): string
    {
        $environment = trim((string) getenv('OPENWEATHERMAP_API_KEY')); if ($environment !== '') { return $environment; }
        $config = $projectRoot . '/config.php'; if (is_file($config)) { require_once $config; }
        if (defined('openweathermap_api_key') && trim((string) constant('openweathermap_api_key')) !== '') { return trim((string) constant('openweathermap_api_key')); }
        throw new WeatherException('No OpenWeatherMap API key is configured. Set OPENWEATHERMAP_API_KEY.');
    }
}
