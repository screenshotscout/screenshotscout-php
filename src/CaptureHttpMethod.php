<?php

declare(strict_types=1);

namespace ScreenshotScout;

/** The SDK-owned request method selector. Service string values remain open. */
enum CaptureHttpMethod: string
{
    case GET = 'GET';
    case POST = 'POST';
}
