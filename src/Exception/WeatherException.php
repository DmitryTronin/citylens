<?php
declare(strict_types=1);
namespace CityLens\Exception;
use RuntimeException;
final class WeatherException extends RuntimeException
{
    public function __construct(private readonly string $safeMessage, ?\Throwable $previous = null) { parent::__construct($safeMessage, 0, $previous); }
    public function safeMessage(): string { return $this->safeMessage; }
}
