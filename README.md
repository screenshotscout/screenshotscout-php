# Screenshot Scout PHP SDK

The official PHP SDK for the [Screenshot Scout](https://screenshotscout.com) screenshot API.

> Status: initial local scaffold. The API client is not implemented or published yet.

The package requires PHP 8.3 or newer and will use the Composer name `screenshotscout/sdk`.
Its blocking client will accept a PSR-18 HTTP transport and PSR-17 request and stream
factories supplied explicitly by the application.

## Repository layout

- `src` contains the PSR-4 `ScreenshotScout` namespace.
- `tests` contains focused SDK tests.
- `examples` will contain runnable usage examples when the client is implemented.

## Development

Install the development dependencies and run the local checks with PHP 8.3 or newer:

```shell
composer install
composer validate --strict
composer format:check
composer analyse
composer test
```

## License

Licensed under the [MIT License](LICENSE).
