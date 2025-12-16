<?php declare(strict_types=1);

namespace App\Framework\Foundation;

interface AppRunner
{
    /**
     * @param array<string, mixed> $server
     * @return void
     */
    function run(array $server): void;

    /**
     * Called when the application is terminating.
     * @return void
     */
    function terminate(): void;
}
