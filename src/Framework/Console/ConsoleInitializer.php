<?php declare(strict_types=1);

namespace Kirameki\Framework\Console;

use Kirameki\Container\ContainerBuilder;
use Kirameki\Framework\Foundation\AppEnv;
use Kirameki\Framework\Foundation\ServiceInitializer;
use Override;

class ConsoleInitializer extends ServiceInitializer
{
    /**
     * @inheritDoc
     */
    #[Override]
    public static function register(ContainerBuilder $container, AppEnv $env): void
    {
        $container->singleton(CommandRegistry::class);
        $container->singleton(CommandRunner::class);
    }
}
