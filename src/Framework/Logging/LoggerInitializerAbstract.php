<?php declare(strict_types=1);

namespace Kirameki\Framework\Logging;

use Kirameki\Container\Container;
use Kirameki\Framework\Foundation\ServiceInitializer;
use Override;

abstract class LoggerInitializerAbstract extends ServiceInitializer
{
    /**
     * @param LoggerBuilder $builder
     * @return Logger
     */
    abstract protected function setup(LoggerBuilder $builder): Logger;

    /**
     * @inheritDoc
     */
    #[Override]
    protected function register(Container $container): void
    {
        $container->singleton(Logger::class, fn() => $this->setup(new LoggerBuilder()));
    }
}
