<?php

declare(strict_types=1);

namespace ScreenshotScout\Internal;

/** The URL a client sends captures to and builds capture URLs on. @internal */
final readonly class CaptureEndpoint
{
    public function __construct(
        public string $url,
        /** Whether the base URL was read from the environment. */
        public bool $fromEnvironment,
    ) {}
}
