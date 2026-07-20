<?php

declare(strict_types=1);

namespace ScreenshotScout\Exception;

final class SerializationException extends \InvalidArgumentException implements ScreenshotScoutException
{
    public function __construct(
        string $message,
        private readonly ?string $option = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function option(): ?string
    {
        return $this->option;
    }
}
