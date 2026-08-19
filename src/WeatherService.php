<?php
declare(strict_types=1);
namespace CityLens;
use CityLens\Cache\FileCache;
final class WeatherService
{
    public function __construct(private readonly OpenWeatherMapClient $client, private readonly FileCache $cache) {}
    public function weatherFor(string $city, string $units): WeatherReport
    {
        try { $cached = $this->cache->get($city, $units); } catch (\Throwable) { $cached = null; }
        if ($cached !== null) { return $cached; }
        $report = $this->client->fetch($city, $units); try { $this->cache->put($city, $units, $report); } catch (\Throwable) {} return $report;
    }
}
