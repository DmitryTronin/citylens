<?php
declare(strict_types=1);
use CityLens\ApiKey;
use CityLens\Cache\FileCache;
use CityLens\Exception\WeatherException;
use CityLens\Http\CurlHttpClient;
use CityLens\IconMapper;
use CityLens\OpenWeatherMapClient;
use CityLens\ReportFormatter;
use CityLens\WeatherReport;
use CityLens\WeatherResponseParser;
use CityLens\WeatherService;
use CityLens\WindClassifier;
require __DIR__ . '/vendor/autoload.php';
/** @param scalar|null $value */
function e(mixed $value): string { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
$city = isset($_GET['city']) ? trim((string) $_GET['city']) : 'Amsterdam';
$units = isset($_GET['units']) ? strtolower(trim((string) $_GET['units'])) : 'metric';
$error = null; $report = null;
if ($city === '') { $error = 'Enter a city name.'; }
elseif (strlen($city) > 100 || preg_match('/[\x00-\x1F\x7F]/', $city)) { $error = 'Enter a valid city name of 100 characters or fewer.'; }
elseif (!in_array($units, ['metric', 'imperial'], true)) { $error = 'Choose metric or imperial units.'; $units = 'metric'; }
else {
    try {
        $client = new OpenWeatherMapClient(new CurlHttpClient(), new WeatherResponseParser(), ApiKey::load(__DIR__));
        $report = (new WeatherService($client, new FileCache(__DIR__ . '/cache', 600)))->weatherFor($city, $units);
    } catch (WeatherException $exception) { $error = $exception->safeMessage(); }
    catch (Throwable $exception) { error_log($exception->getMessage()); $error = 'Something went wrong while loading the weather. Please try again.'; }
}
$tempUnit = $units === 'imperial' ? '°F' : '°C'; $windUnit = $units === 'imperial' ? 'mph' : 'm/s';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="favicon.ico" type="image/x-icon"><link rel="stylesheet" href="assets/styles.css">
  <title>CityLens Weather</title><script src="skycons/skycons.js" defer></script><script src="assets/app.js" defer></script>
</head>
<body><main class="container">
  <header class="app-header"><h1>CityLens Weather</h1><p>Search current weather in any city</p></header>
  <form class="search-form" method="get" role="search">
    <div class="field city-field"><label for="city">City</label><input id="city" name="city" value="<?= e($city) ?>" maxlength="100" required autocomplete="address-level2" placeholder="e.g. Berlin"></div>
    <div class="field"><label for="units">Units</label><select id="units" name="units"><option value="metric" <?= $units === 'metric' ? 'selected' : '' ?>>Metric</option><option value="imperial" <?= $units === 'imperial' ? 'selected' : '' ?>>Imperial</option></select></div>
    <button type="submit">Search</button>
  </form>
  <?php if ($error !== null): ?><div class="alert" role="alert"><strong>Weather unavailable</strong><span><?= e($error) ?></span></div><?php endif; ?>
  <?php if ($report instanceof WeatherReport): ?>
  <section class="weather-card" aria-labelledby="location">
    <div class="weather-icon-container"><canvas id="weather-icon" width="112" height="112" data-icon="<?= e(IconMapper::forWeatherId($report->weatherId)) ?>" role="img" aria-label="<?= e($report->condition) ?>"></canvas></div>
    <h2 id="location"><?= e($report->city) ?>, <?= e($report->country) ?></h2><p class="condition"><?= e(ucfirst($report->condition)) ?></p>
    <dl class="weather-info">
      <div><dt>Temperature</dt><dd><?= e(ReportFormatter::number($report->temperature)) ?><?= e($tempUnit) ?></dd></div>
      <div><dt>Feels like</dt><dd><?= e(ReportFormatter::number($report->feelsLike)) ?><?= e($tempUnit) ?></dd></div>
      <div><dt>Humidity</dt><dd><?= e($report->humidity) ?>%</dd></div>
      <div><dt>Wind</dt><dd><?= e(ReportFormatter::number($report->windSpeed)) ?> <?= e($windUnit) ?> · <?= e(ucfirst(WindClassifier::classify($report->windSpeed, $units))) ?></dd></div>
    </dl>
  </section><?php endif; ?>
  <footer>Powered by OpenWeatherMap</footer>
</main></body></html>
