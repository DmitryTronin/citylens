<?php
declare(strict_types=1);
namespace CityLens;
use CityLens\Exception\WeatherException;
use CityLens\Http\HttpClient;
final class OpenWeatherMapClient
{
    public function __construct(private readonly HttpClient $http, private readonly WeatherResponseParser $parser, private readonly string $apiKey) {}
    public function fetch(string $city, string $units): WeatherReport
    {
        $query = http_build_query(['q' => $city, 'units' => $units, 'lang' => 'en', 'appid' => $this->apiKey]);
        $response = $this->http->get('https://api.openweathermap.org/data/2.5/weather?' . $query);
        if ($response->statusCode >= 200 && $response->statusCode < 300) { return $this->parser->parse($response->body, $units); }
        throw new WeatherException(match ($response->statusCode) { 401, 403 => 'Weather service authentication failed. Please contact the site administrator.', 404 => 'City not found. Check the spelling and try again.', 429 => 'The weather service rate limit has been reached. Please try again later.', default => 'The weather service is temporarily unavailable. Please try again later.' });
    }
}
