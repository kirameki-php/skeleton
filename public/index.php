<?php

use App\Program;

ignore_user_abort(true);

require dirname(__DIR__) . '/vendor/autoload.php';

$program = new Program();

$handler = static fn() => $program->handle($_SERVER);

$program->boot();
while (frankenphp_handle_request($handler)) {
    gc_collect_cycles();
}
$program->shutdown();
