<?php declare(strict_types=1);

namespace Kirameki\Framework\Http;

use Kirameki\Container\Container;
use Kirameki\Framework\Foundation\ServiceInitializer;
use Kirameki\Framework\Http\Routing\HttpRouter;
use Kirameki\Framework\Http\Routing\HttpRouterBuilder;
use Override;

abstract class RoutingInitializerBase extends ServiceInitializer
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function register(Container $container): void
    {
        $container->singleton(HttpRouter::class, function(Container $container): HttpRouter {
            $this->setup($builder = new HttpRouterBuilder($container));
            return $builder->build();
        });
    }

    /**
     * @param HttpRouterBuilder $router
     * @return void
     */
    protected abstract function setup(HttpRouterBuilder $router): void;
}
