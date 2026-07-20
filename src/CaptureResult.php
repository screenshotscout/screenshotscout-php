<?php

declare(strict_types=1);

namespace ScreenshotScout;

/** Documented JSON capture metadata plus unrecognized fields retained without transformation. */
final class CaptureResult
{
    /**
     * @param array<array-key, mixed> $additionalFields
     *
     * @internal SDK-controlled construction.
     */
    public function __construct(
        private readonly ?string $screenshotUrl,
        private readonly ?string $screenshotUrlExpiresAt,
        private readonly ?string $cacheStatus,
        private readonly array $additionalFields,
    ) {}

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

    /** @return array<array-key, mixed> */
    public function additionalFields(): array
    {
        return $this->additionalFields;
    }
}
