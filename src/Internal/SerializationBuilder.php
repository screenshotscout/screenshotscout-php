<?php

declare(strict_types=1);

namespace ScreenshotScout\Internal;

use ScreenshotScout\Exception\SerializationException;

/** @internal */
final class SerializationBuilder
{
    /** @var list<WirePair> */
    private array $pairs;

    /** @var array<string, string|int|float|bool|list<string>> */
    private array $body;

    public function __construct(string $targetUrl)
    {
        self::validateString($targetUrl, 'url');
        $this->pairs = [new WirePair('url', $targetUrl)];
        $this->body = ['url' => $targetUrl];
    }

    public function add(
        string $publicName,
        string $wireName,
        string|int|float|bool|null $value,
    ): void {
        if (null === $value) {
            return;
        }

        if (is_string($value)) {
            self::validateString($value, $publicName);
            $pairValue = $value;
        } elseif (is_float($value)) {
            if (!is_finite($value)) {
                throw new SerializationException(
                    sprintf('The capture option "%s" must be a finite number.', $publicName),
                    $publicName,
                );
            }
            $pairValue = EcmaScriptNumberFormatter::format($value);
        } elseif (is_bool($value)) {
            $pairValue = $value ? 'true' : 'false';
        } else {
            $pairValue = (string) $value;
        }

        $this->pairs[] = new WirePair($wireName, $pairValue);
        $this->body[$wireName] = $value;
    }

    /** @param array<mixed>|null $values */
    public function addRepeated(string $publicName, string $wireName, ?array $values): void
    {
        if (null === $values || [] === $values) {
            return;
        }
        if (!array_is_list($values)) {
            throw new SerializationException(
                sprintf('The capture option "%s" must be a list of strings.', $publicName),
                $publicName,
            );
        }

        $copied = [];
        foreach ($values as $value) {
            if (!is_string($value)) {
                throw new SerializationException(
                    sprintf('The capture option "%s" must contain only strings.', $publicName),
                    $publicName,
                );
            }
            self::validateString($value, $publicName);
            $copied[] = $value;
            $this->pairs[] = new WirePair($wireName, $value);
        }
        $this->body[$wireName] = $copied;
    }

    public function build(): SerializedCaptureOptions
    {
        return new SerializedCaptureOptions($this->pairs, $this->body);
    }

    private static function validateString(string $value, string $publicName): void
    {
        try {
            JsonCodec::encodeString($value);
        } catch (\JsonException $cause) {
            throw new SerializationException(
                sprintf('The capture option "%s" must contain valid UTF-8.', $publicName),
                $publicName,
                $cause,
            );
        }
    }
}
