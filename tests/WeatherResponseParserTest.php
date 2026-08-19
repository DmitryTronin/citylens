<?php
declare(strict_types=1);
namespace CityLens\Tests;
use CityLens\Exception\WeatherException;
use CityLens\WeatherResponseParser;
use PHPUnit\Framework\TestCase;
final class WeatherResponseParserTest extends TestCase
{
    public function testParsesCompleteResponse(): void
    {
        $json = file_get_contents(__DIR__ . '/../data/response-example.json'); self::assertIsString($json);
        $report = (new WeatherResponseParser())->parse($json, 'metric');
        self::assertSame('Amsterdam', $report->city); self::assertSame('US', $report->country); self::assertSame(10.1, $report->temperature); self::assertSame(57, $report->humidity);
    }
    /** @dataProvider invalidResponses */
    public function testRejectsInvalidOrIncompleteResponses(string $json): void
    {
        $this->expectException(WeatherException::class); (new WeatherResponseParser())->parse($json, 'metric');
    }
    public static function invalidResponses(): array { return [['{'], ['{}'], ['null']]; }
}
