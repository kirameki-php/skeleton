<?php declare(strict_types=1);

use Kirameki\Framework\App;

ignore_user_abort(true);

require dirname(__DIR__) . '/vendor/autoload.php';

/** @var App $app */
$app = require __DIR__ . '/app.php';

$exitCode = $app
    ->useHttpRunner()
    ->run();

exit($exitCode);
