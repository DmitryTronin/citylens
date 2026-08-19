<?php
declare(strict_types=1);
namespace CityLens\Tests;
use CityLens\Cli\CliOptions;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
final class CliOptionsTest extends TestCase
{
    public function testDefaultsAndValidOptions(): void
    {
        $defaults = CliOptions::parse(['weather-cli.php']); self::assertSame('Amsterdam', $defaults->city); self::assertSame('metric', $defaults->units);
        $options = CliOptions::parse(['weather-cli.php','--city=Berlin','--units=imperial','--fixture=data/x.json']); self::assertSame('Berlin', $options->city); self::assertSame('imperial', $options->units); self::assertSame('data/x.json', $options->fixture);
    }
    public function testHelp(): void { self::assertTrue(CliOptions::parse(['weather-cli.php','--help'])->help); }
    /** @dataProvider invalidArguments */
    public function testRejectsInvalidArguments(array $arguments): void { $this->expectException(InvalidArgumentException::class); CliOptions::parse($arguments); }
    public static function invalidArguments(): array { return [[['weather-cli.php','--wat']], [['weather-cli.php','--city=']], [['weather-cli.php','--units=kelvin']], [['weather-cli.php','--fixture=']]]; }
}
