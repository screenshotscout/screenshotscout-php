# Examples

Install the development dependencies from the repository root:

```shell
composer install
```

Set `SCREENSHOTSCOUT_ACCESS_KEY`, then run an example:

```shell
php examples/binary.php
php examples/json.php
php examples/capture-url.php
```

`capture-url.php` also reads `SCREENSHOTSCOUT_SECRET_KEY` and signs the URL when it's set. The SDK
creates its default Guzzle transport automatically.
