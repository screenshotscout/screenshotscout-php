<?php

declare(strict_types=1);

namespace ScreenshotScout\Internal;

/** @internal */
final readonly class DecodedJson
{
    public function __construct(
        public mixed $value,
        public mixed $nativeValue,
    ) {}
}
