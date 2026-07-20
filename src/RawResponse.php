<?php

declare(strict_types=1);

namespace ScreenshotScout;

/** Exact buffered HTTP response data retained on successes and API failures. */
final class RawResponse
{
    /** @var array<array-key, list<string>> */
    private readonly array $headers;

    /** @var array<string, list<string>> */
    private readonly array $normalizedHeaders;

    /**
     * @param array<array-key, list<string>> $headers
     *
     * @internal SDK-controlled construction.
     */
    public function __construct(
        private readonly int $statusCode,
        private readonly string $reasonPhrase,
        array $headers,
        private readonly ?string $contentType,
        private readonly string $body,
    ) {
        $this->headers = $headers;
        $normalized = [];
        foreach ($headers as $name => $values) {
            $normalized[strtolower((string) $name)] = $values;
        }
        $this->normalizedHeaders = $normalized;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function reasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /** @return array<array-key, list<string>> */
    public function headers(): array
    {
        return $this->headers;
    }

    /** @return list<string> */
    public function header(string $name): array
    {
        return $this->normalizedHeaders[strtolower($name)] ?? [];
    }

    public function contentType(): ?string
    {
        return $this->contentType;
    }

    /** Returns the exact buffered response body as a binary-safe PHP string. */
    public function body(): string
    {
        return $this->body;
    }
}
