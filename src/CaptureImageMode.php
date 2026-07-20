<?php

declare(strict_types=1);

namespace ScreenshotScout;

/** Documented image resizing modes. CaptureOptions still accepts arbitrary strings. */
final class CaptureImageMode
{
    public const FILL = 'fill';
    public const FIT = 'fit';
    public const STRETCH = 'stretch';

    private function __construct() {}
}
