<?php

declare(strict_types=1);

namespace ScreenshotScout\Internal;

/** @internal */
final readonly class WirePair
{
    public function __construct(
        public string $name,
        public string $value,
    ) {}
}
