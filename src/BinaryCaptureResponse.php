<?php

declare(strict_types=1);

namespace ScreenshotScout;

/** A successful capture whose body contains screenshot, PDF, or Markdown bytes. */
interface BinaryCaptureResponse extends CaptureResponse
{
    /** Returns the exact screenshot, PDF, or Markdown bytes as a binary-safe PHP string. */
    public function bytes(): string;

    public function screenshotUrl(): ?string;

    public function screenshotUrlExpiresAt(): ?string;

    public function cacheStatus(): ?string;

    /** Returns the Markdown text of a Markdown capture, or null for other formats. */
    public function markdown(): ?string;
}
