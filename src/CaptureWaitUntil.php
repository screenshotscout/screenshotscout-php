<?php

declare(strict_types=1);

namespace ScreenshotScout;

/** Documented navigation wait states. CaptureOptions still accepts arbitrary strings. */
final class CaptureWaitUntil
{
    public const DOM_CONTENT_LOADED = 'domcontentloaded';
    public const LOAD = 'load';
    public const NETWORK_IDLE_0 = 'networkidle0';
    public const NETWORK_IDLE_2 = 'networkidle2';

    private function __construct() {}
}
