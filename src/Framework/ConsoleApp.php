<?php declare(strict_types=1);

namespace Kirameki\Framework;

use Kirameki\Framework\Console\CommandRunner;

class ConsoleApp extends App
{
    /**
     * @return void
     */
    public function run(): void
    {
        $this->boot();

        $runner = $this->container->get(CommandRunner::class);

        $exitCode = $this->withScope(static fn() => $runner->runFromArgs($_SERVER['argv'] ?? []));

        $this->terminate();

        exit($exitCode);
    }
}
