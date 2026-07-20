<?php

declare(strict_types=1);

namespace ScreenshotScout;

/** Documented image anchors. CaptureOptions still accepts arbitrary strings. */
final class CaptureImageAnchor
{
    public const BOTTOM = 'bottom';
    public const BOTTOM_LEFT = 'bottom_left';
    public const BOTTOM_RIGHT = 'bottom_right';
    public const CENTER = 'center';
    public const LEFT = 'left';
    public const RIGHT = 'right';
    public const TOP = 'top';
    public const TOP_LEFT = 'top_left';
    public const TOP_RIGHT = 'top_right';

    private function __construct() {}
}
