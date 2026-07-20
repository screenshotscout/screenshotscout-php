<?php

declare(strict_types=1);

namespace ScreenshotScout\Exception;

use ScreenshotScout\RawResponse;

final class ApiException extends \RuntimeException implements ScreenshotScoutException
{
    /**
     * @param list<mixed>|null $errors
     *
     * @internal SDK-controlled construction.
     */
    public function __construct(
        private readonly RawResponse $rawResponse,
        private readonly bool $hasResponseBody,
        private readonly mixed $responseBody,
        private readonly ?string $errorCode,
        private readonly ?string $errorMessage,
        private readonly ?array $errors,
    ) {
        parent::__construct(
            $errorMessage
                ?? sprintf('Screenshot Scout API request failed with status %d.', $rawResponse->statusCode()),
        );
    }

    public function statusCode(): int
    {
        return $this->rawResponse->statusCode();
    }

    public function errorCode(): ?string
    {
        return $this->errorCode;
    }

    public function errorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /** Whether the body was valid JSON, including a valid JSON null. */
    public function hasResponseBody(): bool
    {
        return $this->hasResponseBody;
    }

    /** The decoded JSON value, or null for invalid JSON or a valid JSON null. */
    public function responseBody(): mixed
    {
        return $this->responseBody;
    }

    /** @return list<mixed>|null */
    public function errors(): ?array
    {
        return $this->errors;
    }

    public function rawResponse(): RawResponse
    {
        return $this->rawResponse;
    }
}
