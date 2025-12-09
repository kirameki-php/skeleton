<?php

use App\Program;

ignore_user_abort(true);

require dirname(__DIR__) . '/vendor/autoload.php';

$program = new Program();
$program->boot();

$handler = static function () use ($program): void {
    try {
        echo $program->handle($_SERVER);
    } catch (\Throwable $exception) {
        echo $exception;
    }
};

do {
    $keepRunning = \frankenphp_handle_request($handler);
    $program->afterHandled($keepRunning);
    gc_collect_cycles();
} while ($keepRunning);

$program->shutdown();
