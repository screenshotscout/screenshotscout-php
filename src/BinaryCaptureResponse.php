<?php

declare(strict_types=1);

namespace ScreenshotScout;

/** A successful capture whose body contains screenshot or PDF bytes. */
interface BinaryCaptureResponse extends CaptureResponse
{
    /** Returns the exact screenshot or PDF bytes as a binary-safe PHP string. */
    public function bytes(): string;

    public function screenshotUrl(): ?string;

    public function screenshotUrlExpiresAt(): ?string;

    public function cacheStatus(): ?string;
}
