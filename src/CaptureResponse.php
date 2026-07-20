<?php

declare(strict_types=1);

namespace ScreenshotScout;

/** A successful binary or JSON capture response. */
interface CaptureResponse
{
    public function rawResponse(): RawResponse;
}
