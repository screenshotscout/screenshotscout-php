<?php

declare(strict_types=1);

namespace ScreenshotScout\Internal;

use ScreenshotScout\Exception\ConfigurationException;

/** The keys a client uses, passed by the caller or read from the environment. @internal */
final readonly class Credentials
{
    public const ACCESS_KEY_VARIABLE = 'SCREENSHOTSCOUT_ACCESS_KEY';
    public const SECRET_KEY_VARIABLE = 'SCREENSHOTSCOUT_SECRET_KEY';

    private function __construct(
        public ?string $accessKey,
        public ?string $secretKey,
        /** Whether the keys were read from the environment. */
        public bool $fromEnvironment,
    ) {}

    /**
     * Picks the keys the client uses. Passed keys always win: if either one is passed, neither variable
     * is read. Only when both are null are both variables read, through $lookup.
     *
     * @param \Closure(string): ?string $lookup returns the variable's value, or null when it isn't set
     */
    public static function resolve(
        #[\SensitiveParameter]
        ?string $accessKey,
        #[\SensitiveParameter]
        ?string $secretKey,
        \Closure $lookup,
    ): self {
        if (null !== $accessKey || null !== $secretKey) {
            return new self($accessKey, $secretKey, false);
        }

        $environmentAccessKey = self::readVariable($lookup, self::ACCESS_KEY_VARIABLE);
        $environmentSecretKey = self::readVariable($lookup, self::SECRET_KEY_VARIABLE);
        if (null === $environmentAccessKey) {
            throw new ConfigurationException(sprintf(
                'A non-blank Screenshot Scout access key is required. Pass one, or set %s.',
                self::ACCESS_KEY_VARIABLE,
            ));
        }

        return new self($environmentAccessKey, $environmentSecretKey, true);
    }

    /**
     * An empty value counts as unset, and a whitespace-only value is an error.
     *
     * @param \Closure(string): ?string $lookup
     */
    private static function readVariable(\Closure $lookup, string $name): ?string
    {
        $value = $lookup($name);
        if (null === $value || '' === $value) {
            return null;
        }
        if ('' === trim($value)) {
            throw new ConfigurationException(sprintf('%s is set but blank.', $name));
        }

        return $value;
    }
}
