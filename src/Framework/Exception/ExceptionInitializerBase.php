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
    final public function initialize(): void
    {
        $this->container->singleton(ExceptionHandler::class, function (Container $container): ExceptionHandler {
            $this->build($builder = new ExceptionHandlerBuilder($container));
            return $builder->build();
        });
    }

    /**
     * @param ExceptionHandlerBuilder $handler
     * @return void
     */
    protected abstract function build(ExceptionHandlerBuilder $handler): void;
}
