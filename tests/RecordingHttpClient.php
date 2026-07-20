<?php

declare(strict_types=1);

namespace ScreenshotScout\Tests;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class RecordingHttpClient implements ClientInterface
{
    /** @var list<RequestInterface> */
    private array $requests = [];

    /** @var \Closure(RequestInterface, int): ResponseInterface */
    private readonly \Closure $handler;

    /** @param (callable(RequestInterface, int): ResponseInterface)|null $handler */
    public function __construct(?callable $handler = null)
    {
        $this->handler = null === $handler
            ? static fn(): ResponseInterface => new Response(
                200,
                ['Content-Type' => 'image/png'],
                "\x01",
            )
            : \Closure::fromCallable($handler);
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;

        return ($this->handler)($request, count($this->requests));
    }

    /** @return list<RequestInterface> */
    public function requests(): array
    {
        return $this->requests;
    }
}
