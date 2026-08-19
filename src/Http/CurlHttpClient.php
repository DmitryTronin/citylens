<?php
declare(strict_types=1);
namespace CityLens\Http;
use CityLens\Exception\WeatherException;
final class CurlHttpClient implements HttpClient
{
    public function __construct(private readonly int $timeoutSeconds = 8, private readonly int $connectTimeoutSeconds = 3) {}
    public function get(string $url): HttpResponse
    {
        $handle = curl_init($url);
        if ($handle === false) { throw new WeatherException('Unable to contact the weather service. Please try again.'); }
        curl_setopt_array($handle, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => $this->timeoutSeconds, CURLOPT_CONNECTTIMEOUT => $this->connectTimeoutSeconds, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_USERAGENT => 'CityLens/1.0']);
        $body = curl_exec($handle);
        if ($body === false) { $e = new \RuntimeException(curl_error($handle)); curl_close($handle); throw new WeatherException('Unable to contact the weather service. Please check your connection and try again.', $e); }
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE); curl_close($handle);
        return new HttpResponse($status, $body);
    }
}
