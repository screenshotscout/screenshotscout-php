<?php

declare(strict_types=1);

namespace ScreenshotScout\Internal;

/** @internal */
final readonly class SerializedCaptureOptions
{
    /**
     * @param list<WirePair>                                             $pairs
     * @param array<string, string|int|float|bool|list<string>> $body
     */
    public function __construct(
        public array $pairs,
        public array $body,
    ) {}
}
