<?php declare(strict_types=1);

namespace Kirameki\Framework\Http;

use Kirameki\Container\Container;
use Kirameki\Container\ContainerBuilder;
use Kirameki\Framework\Foundation\AppEnv;
use Kirameki\Framework\Foundation\ServiceInitializer;
use Kirameki\Framework\Http\Commands\ListRoutesCommand;
use Kirameki\Framework\Http\Routing\HttpRouter;
use Kirameki\Framework\Http\Routing\HttpRouterBuilder;
use Override;

class HttpInitializer extends ServiceInitializer
{
    /**
     * @inheritDoc
     */
    #[Override]
    public static function register(ContainerBuilder $container, AppEnv $env): void
    {
        $container->singleton(HttpRouterBuilder::class);
        $container->singleton(HttpRouter::class, static::buildHttpRouter(...));
        $container->singleton(HttpServer::class);
    }

    /**
     * @param Container $container
     * @return HttpRouter
     */
    protected static function buildHttpRouter(Container $container): HttpRouter
    {
        return $container->get(HttpRouterBuilder::class)->build();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    final public function initialize(): void
    {
        $this->commands->add(ListRoutesCommand::class);
    }

}
