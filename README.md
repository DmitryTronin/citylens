# CityLens Weather

CityLens provides current OpenWeatherMap conditions through a responsive web search and a command-line interface. Both interfaces use the same response parser, report model, icon and wind mappings, API error handling, and file cache.

## Requirements and setup

- PHP 8.2 with cURL and JSON
- Composer
- An [OpenWeatherMap API key](https://openweathermap.org/api)

Install dependencies and configure the preferred environment variable:

```bash
composer install
export OPENWEATHERMAP_API_KEY="your-api-key"
```

For backward compatibility, a local, ignored `config.php` is also supported:

```php
<?php
define('openweathermap_api_key', 'your-api-key');
```

Never commit the key or `config.php`.

## Web usage

Serve the project root, then open the displayed address and search by city. Amsterdam and metric units are the defaults.

```bash
php -S localhost:8080
```

Choose Metric for Celsius and m/s or Imperial for Fahrenheit and mph. Submitted values remain selected after the request, including validation or service errors.

## CLI usage

```bash
php src/weather-cli.php --city="Berlin" --units=metric
php src/weather-cli.php --help
```

Options must use `--name=value` syntax. Invalid options and service failures are written to stderr with a non-zero exit status.

### Offline fixture mode

Fixture mode does not load `config.php`, require an API key, use the network, or populate the cache:

```bash
php src/weather-cli.php --city=Amsterdam --units=metric --fixture=data/response-example.json
```

The city option is retained for a consistent command interface; location values come from the fixture response.

## Caching

Successful live responses are stored in the ignored `cache/` directory for 10 minutes. Keys use normalized city names and units. Cache read/write failures are treated as misses and do not break valid API requests.

## Tests and checks

Tests use fixtures and fake HTTP clients, never the real API.

```bash
composer validate --strict
composer test
composer lint
```

## Structure

- `src/` — shared application, HTTP, cache, CLI, and formatting code
- `assets/` — web CSS and JavaScript
- `skycons/` — preserved Skycons library
- `tests/` — PHPUnit unit tests
- `data/response-example.json` — offline example response
