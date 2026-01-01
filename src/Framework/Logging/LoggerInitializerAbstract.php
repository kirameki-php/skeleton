<?php declare(strict_types=1);

namespace Kirameki\Framework\Logging;

use Kirameki\Container\Container;
use Kirameki\Framework\Foundation\ServiceInitializer;
use Override;

abstract class LoggerInitializerAbstract extends ServiceInitializer
{
    /**
     * @param LoggerBuilder $builder
     * @return void
     */
    abstract protected function setup(LoggerBuilder $builder): void;

    /**
     * @inheritDoc
     */
    #[Override]
    public function register(Container $container): void
    {
        $container->singleton(Logger::class, function(): Logger {
            $builder = new LoggerBuilder();
            $this->setup($builder);
            return $builder->build();
        });
    }
}
