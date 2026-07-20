<?php

declare(strict_types=1);

namespace ScreenshotScout\Exception;

final class TransportException extends \RuntimeException implements ScreenshotScoutException
{
    public function __construct(string $message, \Throwable $previous)
    {
        parent::__construct($message, 0, $previous);
    }
}
