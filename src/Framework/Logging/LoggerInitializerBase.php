<?php declare(strict_types=1);

namespace Kirameki\Framework\Logging;

use Kirameki\Container\Container;
use Kirameki\Framework\Foundation\ServiceInitializer;
use Override;

abstract class LoggerInitializerBase extends ServiceInitializer
{
    /**
     * @inheritDoc
     */
    #[Override]
    final public function initialize(): void
    {
        $this->container->singleton(Logger::class, function(): Logger {
            $this->build($builder = new LoggerBuilder());
            return $builder->build();
        });
    }

    /**
     * @param LoggerBuilder $builder
     * @return void
     */
    abstract protected function build(LoggerBuilder $builder): void;

}
