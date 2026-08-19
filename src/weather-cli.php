#!/usr/bin/env php
<?php
declare(strict_types=1);
use CityLens\ApiKey;
use CityLens\Cache\FileCache;
use CityLens\Cli\CliOptions;
use CityLens\Exception\WeatherException;
use CityLens\Http\CurlHttpClient;
use CityLens\OpenWeatherMapClient;
use CityLens\ReportFormatter;
use CityLens\WeatherResponseParser;
use CityLens\WeatherService;
$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
if (!is_file($autoload)) { fwrite(STDERR, "Dependencies are missing. Run composer install.\n"); exit(1); }
require $autoload;
$usage = "Usage: php src/weather-cli.php [--city=\"Berlin\"] [--units=metric|imperial] [--fixture=FILE]\n       php src/weather-cli.php --help\n";
try {
    $options = CliOptions::parse($argv);
    if ($options->help) { fwrite(STDOUT, $usage); exit(0); }
    $parser = new WeatherResponseParser();
    if ($options->fixture !== null) {
        if (!is_file($options->fixture) || !is_readable($options->fixture)) { throw new InvalidArgumentException("Fixture file is not readable: {$options->fixture}"); }
        $contents = file_get_contents($options->fixture);
        if ($contents === false) { throw new InvalidArgumentException("Unable to read fixture: {$options->fixture}"); }
        $report = $parser->parse($contents, $options->units);
    } else {
        $client = new OpenWeatherMapClient(new CurlHttpClient(), $parser, ApiKey::load($root));
        $report = (new WeatherService($client, new FileCache($root . '/cache', 600)))->weatherFor($options->city, $options->units);
    }
    fwrite(STDOUT, ReportFormatter::cli($report));
} catch (InvalidArgumentException $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n\n" . $usage); exit(2);
} catch (WeatherException $e) {
    fwrite(STDERR, 'Error: ' . $e->safeMessage() . "\n"); exit(1);
} catch (Throwable $e) {
    error_log($e->getMessage()); fwrite(STDERR, "Error: An unexpected error occurred.\n"); exit(1);
}
