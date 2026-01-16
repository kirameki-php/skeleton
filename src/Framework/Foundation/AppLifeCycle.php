<?php declare(strict_types=1);

namespace Kirameki\Framework\Foundation;

use Kirameki\Framework\App;

interface AppLifeCycle
{
    function started(App $app): void;

    function terminating(App $app, int $exitCode): void;

    function terminated(App $app, int $exitCode): void;
}
