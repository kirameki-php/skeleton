<?php declare(strict_types=1);

use Kirameki\Framework\AppBuilder;

ignore_user_abort(true);

require dirname(__DIR__) . '/vendor/autoload.php';

/** @var AppBuilder $builder */
$builder = require __DIR__ . '/boot.php';
$builder->useCommandRunner();

$app = $builder->build();

$exitCode = $app->run();

exit($exitCode);
