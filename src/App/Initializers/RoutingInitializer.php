<?php declare(strict_types=1);

namespace Kirameki\App\Initializers;

use Kirameki\Framework\Foundation\ServiceInitializer;
use Kirameki\Framework\Http\Controllers\Health\ReadinessController;
use Kirameki\Framework\Http\Routing\HttpRouterBuilder;
use Kirameki\Framework\Http\Routing\ResourceOptions;

class RoutingInitializer extends ServiceInitializer
{
    public function initialize(): void
    {
        $this->configure(function (HttpRouterBuilder $router) {
            $router->healthChecks();
            $router->resources('friends', ReadinessController::class, ResourceOptions::default());
            $router->namespace('users', function (HttpRouterBuilder $router) {
                $router->get('/', ReadinessController::class);
                $router->get('/{id|int}', ReadinessController::class);
                $router->get('/{id}', ReadinessController::class);
            });
        });
    }
}
