<?php
declare(strict_types=1);
namespace CityLens\Cli;
use InvalidArgumentException;
final readonly class CliOptions
{
    public function __construct(public string $city, public string $units, public ?string $fixture, public bool $help) {}
    /** @param list<string> $arguments */
    public static function parse(array $arguments): self
    {
        $city = 'Amsterdam'; $units = 'metric'; $fixture = null; $help = false;
        foreach (array_slice($arguments, 1) as $argument) {
            if ($argument === '--help' || $argument === '-h') { $help = true; continue; }
            if (str_starts_with($argument, '--city=')) { $city = trim(substr($argument, 7)); continue; }
            if (str_starts_with($argument, '--units=')) { $units = strtolower(trim(substr($argument, 8))); continue; }
            if (str_starts_with($argument, '--fixture=')) { $fixture = trim(substr($argument, 10)); continue; }
            throw new InvalidArgumentException("Unknown argument: {$argument}");
        }
        if ($city === '') { throw new InvalidArgumentException('City must not be empty.'); }
        if (!in_array($units, ['metric', 'imperial'], true)) { throw new InvalidArgumentException('Units must be metric or imperial.'); }
        if ($fixture === '') { throw new InvalidArgumentException('Fixture path must not be empty.'); }
        return new self($city, $units, $fixture, $help);
    }
}
