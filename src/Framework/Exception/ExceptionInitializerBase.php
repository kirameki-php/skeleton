<?php declare(strict_types=1);

namespace Kirameki\Framework\Exception;

use Kirameki\Container\Container;
use Kirameki\Framework\Foundation\ServiceInitializer;
use Override;

abstract class ExceptionInitializerBase extends ServiceInitializer
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function register(Container $container): void
    {
        $container->singleton(ExceptionHandler::class, function () use ($container): ExceptionHandler {
            $this->setup($builder = new ExceptionHandlerBuilder($container));
            return $builder->build();
        });
    }

    /**
     * @param ExceptionHandlerBuilder $handler
     * @return void
     */
    protected abstract function setup(ExceptionHandlerBuilder $handler): void;
}
