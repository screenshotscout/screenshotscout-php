<?php

declare(strict_types=1);

namespace ScreenshotScout\Internal;

use ScreenshotScout\CaptureResult;
use ScreenshotScout\JsonCaptureResponse;
use ScreenshotScout\RawResponse;

/** @internal */
final class DefaultJsonCaptureResponse implements JsonCaptureResponse
{
    public function __construct(
        private readonly CaptureResult $result,
        private readonly RawResponse $rawResponse,
    ) {}

    public function result(): CaptureResult
    {
        return $this->result;
    }

    public function rawResponse(): RawResponse
    {
        return $this->rawResponse;
    }
}
