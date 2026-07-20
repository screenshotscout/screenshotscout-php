<?php

declare(strict_types=1);

namespace ScreenshotScout;

/** Documented CSS media types. CaptureOptions still accepts arbitrary strings. */
final class CaptureMediaType
{
    public const PRINT = 'print';
    public const SCREEN = 'screen';

    private function __construct() {}
}
