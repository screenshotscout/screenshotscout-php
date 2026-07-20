<?php

declare(strict_types=1);

namespace ScreenshotScout;

/** Documented color schemes. CaptureOptions still accepts arbitrary strings. */
final class CaptureColorScheme
{
    public const AUTO = 'auto';
    public const DARK = 'dark';
    public const LIGHT = 'light';

    private function __construct() {}
}
