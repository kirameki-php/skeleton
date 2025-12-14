<?php

use App\Framework\App;
use Kirameki\Storage\Path;

ignore_user_abort(true);

require dirname(__DIR__) . '/vendor/autoload.php';

$path = Path::of(dirname(__DIR__));
chdir($path->toString());

$app = new App($path);

$app->boot();

$handler = static fn() => $app->handle($_SERVER);
while (frankenphp_handle_request($handler)) {
    gc_collect_cycles();
}

$app->terminate();
