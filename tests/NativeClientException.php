<?php

declare(strict_types=1);

namespace ScreenshotScout\Tests;

use Psr\Http\Client\ClientExceptionInterface;

final class NativeClientException extends \RuntimeException implements ClientExceptionInterface {}
