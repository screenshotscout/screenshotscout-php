<?php

declare(strict_types=1);

namespace ScreenshotScout;

/** A successful capture whose body contains a JSON result. */
interface JsonCaptureResponse extends CaptureResponse
{
    public function result(): CaptureResult;
}
