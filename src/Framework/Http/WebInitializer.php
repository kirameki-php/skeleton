<?php declare(strict_types=1);

namespace Kirameki\Framework\Http;

use Kirameki\Container\Container;
use Kirameki\Framework\Foundation\ServiceInitializer;
use Kirameki\Framework\Http\Commands\ListRoutesCommand;
use Kirameki\Framework\Http\Routing\HttpRouter;
use Kirameki\Framework\Http\Routing\HttpRouterBuilder;
use Override;

class WebInitializer extends ServiceInitializer
{
    /**
     * @inheritDoc
     */
    #[Override]
    final public function initialize(): void
    {
        $container = $this->container;
        $container->singleton(HttpRouterBuilder::class);
        $container->singleton(HttpRouter::class, $this->buildHttpRouter(...));
        $container->singleton(HttpServer::class);

        $this->commands->add(ListRoutesCommand::class);
    }

    /**
     * @param Container $container
     * @return HttpRouter
     */
    protected function buildHttpRouter(Container $container): HttpRouter
    {
        return $container->get(HttpRouterBuilder::class)->build();
    }
}
