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
php examples/html.php
```

If `SCREENSHOTSCOUT_SECRET_KEY` is also set, the client signs its requests and the URL that
`capture-url.php` prints. `html.php` renders an invoice to PDF and sends the HTML in a POST request,
which can't be signed, so run it without `SCREENSHOTSCOUT_SECRET_KEY`. The SDK creates its default
Guzzle transport automatically.
