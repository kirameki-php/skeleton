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
    final public function initialize(): void
    {
        $this->container->singleton(CommandRegistry::class);
        $this->container->singleton(CommandRunner::class);
    }
}
