<?php

declare(strict_types=1);

namespace ScreenshotScout\Internal;

/** @internal */
final class Environment
{
    /**
     * Returns the variable's value, or null when it isn't set. A string in $_ENV comes first, because
     * .env loaders such as Symfony Dotenv don't call putenv() by default.
     */
    public static function read(string $name): ?string
    {
        $value = $_ENV[$name] ?? null;
        if (is_string($value)) {
            return $value;
        }

        $value = getenv($name);

        return false === $value ? null : $value;
    }

    private function __construct() {}
}
