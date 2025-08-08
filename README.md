# CityLens Weather

A weather application that provides real-time weather information using the OpenWeatherMap API. Includes both web interface and command-line interface.

## Features

- **Web Interface** (`index.php`): Visual weather display with animated weather icons using Skycons
- **CLI Interface** (`src/weather-cli.php`): Command-line weather reports with logging
- **Mock Data Support** (`src/weather-mock.php`): Testing without API calls
- **Responsive Design**: Clean, centered layout with weather cards

## Requirements

- PHP 8.2+
- cURL extension
- OpenWeatherMap API key

## Setup

1. Get your API key at https://openweathermap.org/api

2. Create `config.php` in the root directory:
```php
<?php
define('openweathermap_api_key', 'your-api-key-here');
```

3. For API requests by city ID, find city IDs at https://bulk.openweathermap.org/sample/

## Usage

- **Web**: Access `index.php` in your browser
- **CLI**: Run `php src/weather-cli.php` from the command line

## Project Structure

```
/
├── index.php              # Main web interface
├── config.php             # API configuration (create manually)
├── composer.json          # PHP dependencies
├── src/
│   ├── weather.php        # Core weather fetching logic
│   ├── weather-cli.php    # Command-line interface
│   ├── weather-mock.php   # Mock data for testing
│   ├── time-date.php      # Date/time utilities
│   └── version.php        # Version information
├── skycons/               # Weather icon library
├── data/                  # Sample API responses
└── logs/                  # CLI logging output
```
