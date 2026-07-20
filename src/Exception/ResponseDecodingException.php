<?php

declare(strict_types=1);

namespace ScreenshotScout\Exception;

use ScreenshotScout\RawResponse;

final class ResponseDecodingException extends \RuntimeException implements ScreenshotScoutException
{
    public function __construct(
        string $message,
        private readonly RawResponse $rawResponse,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function rawResponse(): RawResponse
    {
        return $this->rawResponse;
    }
}
