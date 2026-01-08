<?php declare(strict_types=1);

use Kirameki\Framework\AppBuilder;

require dirname(__DIR__) . '/vendor/autoload.php';

/** @var AppBuilder $builder */
$builder = require __DIR__ . '/boot.php';

$builder
    ->buildForConsole()
    ->run();
