<?php

use Kirameki\App\Initializers\ExceptionInitializer;
use Kirameki\App\Initializers\LoggerInitializer;
use Kirameki\App\Initializers\RoutingInitializer;
use Kirameki\Framework\App;
use Kirameki\Storage\Path;

require dirname(__DIR__) . '/vendor/autoload.php';

$path = Path::of(dirname(__DIR__));
chdir($path->toString());

$app = new App($path);

$app->boot([
    LoggerInitializer::class,
    ExceptionInitializer::class,
    RoutingInitializer::class,
]);

$exitCode = $app->runCommand($argv);

$app->terminate();

exit($exitCode);
