<?php

use Kirameki\App\Initializers\ExceptionInitializer;
use Kirameki\App\Initializers\LoggerInitializer;
use Kirameki\App\Initializers\RoutingInitializer;
use Kirameki\Dumper\Config;
use Kirameki\Dumper\Dumper;
use Kirameki\Framework\App;
use Kirameki\Storage\Path;

ignore_user_abort(true);

require dirname(__DIR__) . '/vendor/autoload.php';

Dumper::setInstance(new Dumper(new Config(decorator: 'cli')));

$path = Path::of(dirname(__DIR__));
chdir($path->toString());

$app = new App($path);

$app->boot([
    LoggerInitializer::class,
    ExceptionInitializer::class,
    RoutingInitializer::class,
]);

$handler = static fn() => $app->handleHttp($_SERVER);

while (frankenphp_handle_request($handler)) {
    gc_collect_cycles();
}

$app->terminate();
