<?php

use Kirameki\App\Initializers\RoutesInitializer;
use Kirameki\Framework\App;
use Kirameki\Storage\Path;

ignore_user_abort(true);

require __DIR__ . '/vendor/autoload.php';

$path = Path::of(__DIR__);
chdir($path->toString());

$app = new App($path);

$app->boot([
    RoutesInitializer::class,
]);

$handler = static fn() => $app->handleHttp($_SERVER);
while (frankenphp_handle_request($handler)) {
    gc_collect_cycles();
}

$app->terminate();
