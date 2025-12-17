<?php declare(strict_types=1);

namespace Kirameki\Framework\Foundation;

use Kirameki\Framework\App;

interface AppLifeCycle
{
    function started(App $app): void;

    function terminating(App $app): void;

    function terminated(App $app): void;
}
