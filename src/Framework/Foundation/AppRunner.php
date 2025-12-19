<?php declare(strict_types=1);

namespace Kirameki\Framework\Foundation;

interface AppRunner
{
    /**
     * @param AppScope $scope
     * @param array<string, mixed> $server
     * @return void
     */
    function run(AppScope $scope, array $server): void;

    /**
     * Called when the application is terminating.
     * @return void
     */
    function terminate(): void;
}
