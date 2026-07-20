<?php

declare(strict_types=1);

namespace ScreenshotScout;

/** Documented capture formats. CaptureOptions still accepts arbitrary strings. */
final class CaptureFormat
{
    public const GIF = 'gif';
    public const JPEG = 'jpeg';
    public const JPG = 'jpg';
    public const PDF = 'pdf';
    public const PNG = 'png';
    public const TIFF = 'tiff';
    public const WEBP = 'webp';

    private function __construct() {}
}
