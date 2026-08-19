<?php
declare(strict_types=1);
namespace CityLens\Http;
interface HttpClient { public function get(string $url): HttpResponse; }
