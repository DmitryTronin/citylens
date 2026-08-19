<?php
declare(strict_types=1);
namespace CityLens;
final class ReportFormatter
{
    public static function cli(WeatherReport $report): string
    {
        $temperature = $report->units === 'imperial' ? '°F' : '°C';
        $wind = $report->units === 'imperial' ? 'mph' : 'm/s';
        return sprintf("Location: %s, %s\nCondition: %s\nTemperature: %s%s\nFeels like: %s%s\nHumidity: %d%%\nWind: %s %s - %s\n", $report->city, $report->country, ucfirst($report->condition), self::number($report->temperature), $temperature, self::number($report->feelsLike), $temperature, $report->humidity, self::number($report->windSpeed), $wind, ucfirst(WindClassifier::classify($report->windSpeed, $report->units)));
    }
    public static function number(float $value): string { return rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.'); }
}
