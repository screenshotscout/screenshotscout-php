<?php

declare(strict_types=1);

namespace ScreenshotScout;

/** Documented storage modes. CaptureOptions still accepts arbitrary strings. */
final class CaptureStorageMode
{
    public const EXTERNAL = 'external';
    public const MANAGED = 'managed';

    private function __construct() {}
}
