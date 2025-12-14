<?php declare(strict_types=1);

namespace App\Framework\Foundation;

interface AppRunner
{
    function run(): void;

    /**
     * Called when the application is terminating.
     */
    function terminate(): void;
}
