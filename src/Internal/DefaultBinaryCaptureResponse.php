<?php

declare(strict_types=1);

namespace ScreenshotScout\Internal;

use ScreenshotScout\BinaryCaptureResponse;
use ScreenshotScout\RawResponse;

/** @internal */
final class DefaultBinaryCaptureResponse implements BinaryCaptureResponse
{
    public function __construct(
        private readonly RawResponse $rawResponse,
        private readonly ?string $screenshotUrl,
        private readonly ?string $screenshotUrlExpiresAt,
        private readonly ?string $cacheStatus,
    ) {}

    public function bytes(): string
    {
        return $this->rawResponse->body();
    }

    public function screenshotUrl(): ?string
    {
        return $this->screenshotUrl;
    }

    public function screenshotUrlExpiresAt(): ?string
    {
        return $this->screenshotUrlExpiresAt;
    }

    public function cacheStatus(): ?string
    {
        return $this->cacheStatus;
    }

    public function rawResponse(): RawResponse
    {
        return $this->rawResponse;
    }
}
