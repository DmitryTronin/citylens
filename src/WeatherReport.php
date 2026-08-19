<?php
declare(strict_types=1);
namespace CityLens;
final readonly class WeatherReport
{
    public function __construct(public string $city, public string $country, public string $condition, public int $weatherId, public float $temperature, public float $feelsLike, public int $humidity, public float $windSpeed, public string $units) {}
    /** @return array<string, int|float|string> */
    public function toArray(): array { return get_object_vars($this); }
    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self { return new self((string) $data['city'], (string) $data['country'], (string) $data['condition'], (int) $data['weatherId'], (float) $data['temperature'], (float) $data['feelsLike'], (int) $data['humidity'], (float) $data['windSpeed'], (string) $data['units']); }
}
