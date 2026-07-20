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

Set `SCREENSHOTSCOUT_SECRET_KEY` as well when signed requests are enabled for the API key.
The SDK creates its default Guzzle transport automatically.
