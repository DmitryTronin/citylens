<?php
declare(strict_types=1);
namespace CityLens;
final class WindClassifier
{
    public static function classify(float $speed, string $units): string
    {
        $ms = $units === 'imperial' ? $speed * 0.44704 : $speed;
        return match (true) { $ms < 1 => 'almost no wind', $ms <= 10 => 'light breeze', $ms <= 20 => 'moderate wind', $ms <= 30 => 'strong wind', default => 'storm outside' };
    }
}
