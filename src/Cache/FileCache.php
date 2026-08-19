<?php
declare(strict_types=1);
namespace CityLens\Cache;
use CityLens\WeatherReport;
final class FileCache
{
    public function __construct(private readonly string $directory, private readonly int $ttl = 600) {}
    public function get(string $city, string $units): ?WeatherReport
    {
        $file = $this->path($city, $units); $modified = @filemtime($file);
        if (!is_file($file) || $modified === false || $modified + $this->ttl < time()) { return null; }
        $contents = @file_get_contents($file); $data = $contents === false ? null : json_decode($contents, true);
        if (!is_array($data) || array_diff(['city', 'country', 'condition', 'weatherId', 'temperature', 'feelsLike', 'humidity', 'windSpeed', 'units'], array_keys($data)) !== []) { return null; }
        try { return WeatherReport::fromArray($data); } catch (\Throwable) { return null; }
    }
    public function put(string $city, string $units, WeatherReport $report): void
    {
        if (!is_dir($this->directory) && !@mkdir($this->directory, 0775, true) && !is_dir($this->directory)) { return; }
        try { $json = json_encode($report->toArray(), JSON_THROW_ON_ERROR); } catch (\Throwable) { return; }
        @file_put_contents($this->path($city, $units), $json, LOCK_EX);
    }
    public function path(string $city, string $units): string { return $this->directory . '/' . hash('sha256', self::normalizeCity($city) . '|' . strtolower($units)) . '.json'; }
    private static function normalizeCity(string $city): string { return strtolower(trim((string) preg_replace('/\s+/', ' ', $city))); }
}
