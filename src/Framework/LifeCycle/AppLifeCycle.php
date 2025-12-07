<?php declare(strict_types=1);

namespace App\Framework\LifeCycle;

use App\Framework\App;

interface AppLifeCycle
{
    function started(App $app): void;

    function terminating(App $app): void;

    function terminated(App $app): void;
}
