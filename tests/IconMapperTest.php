<?php
declare(strict_types=1);
namespace CityLens\Tests;
use CityLens\IconMapper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
final class IconMapperTest extends TestCase
{
    #[DataProvider('icons')]
    public function testMapsWeatherCodes(int $id, string $expected): void { self::assertSame($expected, IconMapper::forWeatherId($id)); }
    public static function icons(): array { return [[201,'THUNDER'],[311,'SLEET'],[500,'RAIN'],[511,'SNOW'],[612,'SLEET'],[621,'SNOW'],[741,'FOG'],[781,'WIND'],[800,'CLEAR_DAY'],[801,'PARTLY_CLOUDY_DAY'],[804,'CLOUDY'],[999,'PARTLY_CLOUDY_DAY']]; }
}
