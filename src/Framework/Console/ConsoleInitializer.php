<?php declare(strict_types=1);

namespace Kirameki\Framework\Console;

use Kirameki\Framework\Foundation\ServiceInitializer;
use Override;

class ConsoleInitializer extends ServiceInitializer
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function initialize(): void
    {
        $this->container->singleton(CommandRunner::class);
        $this->container->instance(CommandRegistry::class, $this->commands);
    }
}
