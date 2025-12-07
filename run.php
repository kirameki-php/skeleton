<?php

use App\Program;

require __DIR__.'/vendor/autoload.php';

ignore_user_abort(true);

$program = new Program();
$program->boot();

$handler = static fn(array $server) => $program->handle($_SERVER);

do {
    $keepRunning = frankenphp_handle_request($handler);
    $program->afterHandled($keepRunning);
    gc_collect_cycles();
} while ($keepRunning);

$program->shutdown();
