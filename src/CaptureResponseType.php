<?php

declare(strict_types=1);

namespace ScreenshotScout;

/** Documented response representations. CaptureOptions still accepts arbitrary strings. */
final class CaptureResponseType
{
    public const BINARY = 'binary';
    public const JSON = 'json';

    private function __construct() {}
}
