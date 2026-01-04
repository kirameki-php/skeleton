<?php declare(strict_types=1);

namespace Kirameki\Framework\Http;

use Kirameki\Container\Container;
use Kirameki\Framework\Foundation\ServiceInitializer;
use Kirameki\Framework\Http\Routing\Commands\ListCommand;
use Kirameki\Framework\Http\Routing\HttpRouter;
use Kirameki\Framework\Http\Routing\HttpRouterBuilder;
use Override;

abstract class RoutingInitializerBase extends ServiceInitializer
{
    /**
     * @inheritDoc
     */
    #[Override]
    final public function register(Container $container): void
    {
        $container->singleton(HttpRouter::class, function(Container $container): HttpRouter {
            $this->setup($builder = new HttpRouterBuilder($container));
            return $builder->build();
        });

        $this->addCommands([
            ListCommand::class,
        ]);
    }

    /**
     * @param HttpRouterBuilder $router
     * @return void
     */
    protected abstract function setup(HttpRouterBuilder $router): void;
}
