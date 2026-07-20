<?php

declare(strict_types=1);

namespace ScreenshotScout\Internal;

/** Formats IEEE-754 values the same way as JavaScript String(number)/JSON.stringify. @internal */
final class EcmaScriptNumberFormatter
{
    public static function format(float $value): string
    {
        if (!is_finite($value)) {
            throw new \InvalidArgumentException('Expected a finite floating-point value.');
        }
        if (0.0 === $value) {
            return '0';
        }

        $encoded = self::encodeShortestRoundTrip($value);
        if (!str_contains(strtolower($encoded), 'e')) {
            return $encoded;
        }

        if (1 !== preg_match('/^(-?)([0-9])(?:\.([0-9]+))?[eE]([+-]?[0-9]+)$/D', $encoded, $matches)) {
            throw new \JsonException('Unexpected floating-point encoding.');
        }

        $negative = '-' === $matches[1];
        $digits = rtrim($matches[2] . $matches[3], '0');
        if ('' === $digits) {
            $digits = '0';
        }
        $exponent = (int) $matches[4];
        $decimalPosition = $exponent + 1;

        if (0 < $decimalPosition && $decimalPosition <= 21) {
            $number = strlen($digits) <= $decimalPosition
                ? $digits . str_repeat('0', $decimalPosition - strlen($digits))
                : substr($digits, 0, $decimalPosition) . '.' . substr($digits, $decimalPosition);
        } elseif (-6 < $decimalPosition && $decimalPosition <= 0) {
            $number = '0.' . str_repeat('0', -$decimalPosition) . $digits;
        } else {
            $number = $digits[0];
            if (1 < strlen($digits)) {
                $number .= '.' . substr($digits, 1);
            }
            $number .= 'e' . (0 <= $exponent ? '+' : '') . $exponent;
        }

        return $negative ? '-' . $number : $number;
    }

    private static function encodeShortestRoundTrip(float $value): string
    {
        $precision = ini_get('serialize_precision');
        if (false === $precision) {
            throw new \JsonException('PHP serialize_precision could not be read.');
        }
        if ('-1' === $precision) {
            return json_encode($value, JSON_THROW_ON_ERROR);
        }
        if (false === ini_set('serialize_precision', '-1')) {
            throw new \JsonException('PHP serialize_precision could not be controlled.');
        }

        try {
            return json_encode($value, JSON_THROW_ON_ERROR);
        } finally {
            if (false === ini_set('serialize_precision', $precision)) {
                throw new \JsonException('PHP serialize_precision could not be restored.');
            }
        }
    }

    private function __construct() {}
}
