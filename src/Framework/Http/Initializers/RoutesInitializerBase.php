<?php declare(strict_types=1);

namespace Kirameki\Framework\Http\Initializers;

use Kirameki\Container\Container;
use Kirameki\Framework\Foundation\ServiceInitializer;
use Kirameki\Framework\Http\Routing\HttpRouter;
use Kirameki\Framework\Http\Routing\HttpRouterBuilder;

abstract class RoutesInitializerBase implements ServiceInitializer
{
    public function register(Container $container): void
    {
        $container->singleton(HttpRouter::class, function(Container $container) {
            return $this->setupRoutes(new HttpRouterBuilder($container));
        });
    }

    /**
     * @param HttpRouterBuilder $router
     * @return HttpRouter
     */
    protected abstract function setupRoutes(HttpRouterBuilder $router): HttpRouter;
}
