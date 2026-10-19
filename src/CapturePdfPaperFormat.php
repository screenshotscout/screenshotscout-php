<?php

declare(strict_types=1);

namespace ScreenshotScout;

/** Documented PDF paper formats. CaptureOptions still accepts arbitrary strings. */
final class CapturePdfPaperFormat
{
    public const A3 = 'a3';
    public const A4 = 'a4';
    public const A5 = 'a5';
    public const A6 = 'a6';
    public const LEGAL = 'legal';
    public const LETTER = 'letter';
    public const TABLOID = 'tabloid';

    private function __construct() {}
}
