<?php

declare(strict_types=1);

namespace ScreenshotScout\Internal;

/** @internal */
final class JsonCodec
{
    private const ENCODE_FLAGS = JSON_THROW_ON_ERROR
        | JSON_UNESCAPED_LINE_TERMINATORS
        | JSON_UNESCAPED_SLASHES
        | JSON_UNESCAPED_UNICODE;

    /** @param array<string, string|int|float|bool|list<string>> $value */
    public static function encodeWireObject(array $value): string
    {
        $fields = [];
        foreach ($value as $name => $fieldValue) {
            $fields[] = self::encodeString($name) . ':' . self::encodeWireValue($fieldValue);
        }

        return '{' . implode(',', $fields) . '}';
    }

    public static function decode(string $body): DecodedJson
    {
        $decoded = json_decode($body, false, 512, JSON_THROW_ON_ERROR);

        return new DecodedJson(
            $decoded instanceof \stdClass,
            self::normalize($decoded),
            $decoded,
        );
    }

    /** @return array<array-key, mixed>|null */
    public static function objectFields(DecodedJson $decoded): ?array
    {
        if (!$decoded->nativeValue instanceof \stdClass) {
            return null;
        }

        $fields = [];
        foreach (get_object_vars($decoded->nativeValue) as $name => $value) {
            $fields[$name] = self::normalize($value);
        }

        return $fields;
    }

    public static function encodeString(string $value): string
    {
        $encoded = json_encode($value, self::ENCODE_FLAGS);
        return $encoded;
    }

    /** @param string|int|float|bool|list<string> $value */
    private static function encodeWireValue(string|int|float|bool|array $value): string
    {
        if (is_string($value)) {
            return self::encodeString($value);
        }
        if (is_int($value)) {
            return (string) $value;
        }
        if (is_float($value)) {
            return EcmaScriptNumberFormatter::format($value);
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        $items = [];
        foreach ($value as $item) {
            $items[] = self::encodeString($item);
        }

        return '[' . implode(',', $items) . ']';
    }

    private static function normalize(mixed $value): mixed
    {
        if ($value instanceof \stdClass) {
            $result = [];
            foreach (get_object_vars($value) as $name => $fieldValue) {
                $result[$name] = self::normalize($fieldValue);
            }

            return $result;
        }
        if (is_array($value)) {
            $result = [];
            foreach ($value as $key => $item) {
                $result[$key] = self::normalize($item);
            }

            return $result;
        }

        return $value;
    }

    private function __construct() {}
}
