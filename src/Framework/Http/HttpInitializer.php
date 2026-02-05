<?php declare(strict_types=1);

namespace Kirameki\Framework\Http;

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
    public function initialize(): void
    {
        $this->container->singleton(HttpServer::class);
        $this->container->builder(HttpRouterBuilder::class, HttpRouter::class);

        $this->commands->add(ListRoutesCommand::class);
    }
}
