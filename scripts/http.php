<?php declare(strict_types=1);

use Kirameki\App\Initializers\ExceptionInitializer;
use Kirameki\App\Initializers\LoggerInitializer;
use Kirameki\App\Initializers\RoutingInitializer;
use Kirameki\Framework\AppBuilder;

ignore_user_abort(true);

require dirname(__DIR__) . '/vendor/autoload.php';

/** @var AppBuilder $builder */
$builder = require __DIR__ . '/boot.php';

$builder
    ->buildForWeb()
    ->run();
