<?php declare(strict_types=1);

namespace Kirameki\Framework;

use Kirameki\Framework\Foundation\AppScope;
use Kirameki\Framework\Http\HttpServer;

class WebApp extends App
{
    /**
     * @return void
     */
    public function run(): void
    {
        $this->boot();

        $server = $this->container->get(HttpServer::class);
        $handler = fn() => $this->handle($server);

        while (frankenphp_handle_request($handler)) {
            gc_collect_cycles();
        }

        $this->terminate();
    }

    /**
     * @param HttpServer $server
     * @return void
     */
    public function handle(HttpServer $server): void
    {
        $this->withScope(static fn(AppScope $scope) => $server->run($scope, $_SERVER));
    }
}
