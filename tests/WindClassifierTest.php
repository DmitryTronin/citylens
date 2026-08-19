<?php
declare(strict_types=1);
namespace CityLens\Tests;
use CityLens\WindClassifier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
final class WindClassifierTest extends TestCase
{
    #[DataProvider('winds')]
    public function testClassifiesInBothUnitSystems(float $speed, string $units, string $expected): void { self::assertSame($expected, WindClassifier::classify($speed, $units)); }
    public static function winds(): array { return [[0.5,'metric','almost no wind'],[10.0,'metric','light breeze'],[15.0,'metric','moderate wind'],[25.0,'metric','strong wind'],[31.0,'metric','storm outside'],[2.0,'imperial','almost no wind'],[22.0,'imperial','light breeze'],[30.0,'imperial','moderate wind']]; }
}
