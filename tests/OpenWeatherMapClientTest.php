<?php
declare(strict_types=1);
namespace CityLens\Tests;
use CityLens\Exception\WeatherException;
use CityLens\Http\HttpClient;
use CityLens\Http\HttpResponse;
use CityLens\OpenWeatherMapClient;
use CityLens\WeatherResponseParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
final class OpenWeatherMapClientTest extends TestCase
{
    #[DataProvider('errors')]
    public function testMapsApiErrors(int $status, string $message): void
    {
        $http = new class($status) implements HttpClient { public function __construct(private int $status) {} public function get(string $url): HttpResponse { return new HttpResponse($this->status, '{}'); } };
        $client = new OpenWeatherMapClient($http, new WeatherResponseParser(), 'secret');
        try { $client->fetch('Nowhere', 'metric'); self::fail('Expected exception'); } catch (WeatherException $e) { self::assertSame($message, $e->safeMessage()); }
    }
    public static function errors(): array { return [[401,'Weather service authentication failed. Please contact the site administrator.'],[404,'City not found. Check the spelling and try again.'],[429,'The weather service rate limit has been reached. Please try again later.'],[500,'The weather service is temporarily unavailable. Please try again later.']]; }
    public function testBuildsEncodedCityRequest(): void
    {
        $fixture = file_get_contents(__DIR__ . '/../data/response-example.json'); self::assertIsString($fixture);
        $http = new class($fixture) implements HttpClient { public string $url=''; public function __construct(private string $body) {} public function get(string $url): HttpResponse { $this->url=$url; return new HttpResponse(200,$this->body); } };
        (new OpenWeatherMapClient($http,new WeatherResponseParser(),'secret'))->fetch('New York','metric'); self::assertStringContainsString('q=New+York', $http->url); self::assertStringContainsString('units=metric', $http->url);
    }
}
