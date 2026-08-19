<?php
declare(strict_types=1);
namespace CityLens;
use CityLens\Exception\WeatherException;
use JsonException;
final class WeatherResponseParser
{
    public function parse(string $json, string $units): WeatherReport
    {
        try { $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR); }
        catch (JsonException $e) { throw new WeatherException('The weather service returned an invalid response. Please try again.', $e); }
        if (!is_array($data)) { throw new WeatherException('The weather service returned an invalid response. Please try again.'); }
        $v = [$data['name'] ?? null, $data['sys']['country'] ?? null, $data['weather'][0]['description'] ?? null, $data['weather'][0]['id'] ?? null, $data['main']['temp'] ?? null, $data['main']['feels_like'] ?? null, $data['main']['humidity'] ?? null, $data['wind']['speed'] ?? null];
        if (in_array(null, $v, true)) { throw new WeatherException('The weather service response was incomplete. Please try again.'); }
        return new WeatherReport((string)$v[0], (string)$v[1], (string)$v[2], (int)$v[3], (float)$v[4], (float)$v[5], (int)$v[6], (float)$v[7], $units);
    }
}
