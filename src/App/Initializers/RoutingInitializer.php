<?php declare(strict_types=1);

namespace Kirameki\App\Initializers;

use Kirameki\Framework\Http\Controllers\Health\ReadinessController;
use Kirameki\Framework\Http\Routing\HttpRouterBuilder;
use Kirameki\Framework\Http\Routing\ResourceOptions;
use Kirameki\Framework\Http\RoutingInitializerBase;

class RoutingInitializer extends RoutingInitializerBase
{
    protected function setup(HttpRouterBuilder $router): void
    {
        $router
            ->get('/readyz', ReadinessController::class)
            ->resources('friends', ReadinessController::class, ResourceOptions::default())
            ->namespace('users', function (HttpRouterBuilder $router) {
                $router
                    ->get('/', ReadinessController::class)
                    ->get('/{id|int}', ReadinessController::class)
                    ->get('/{id}', ReadinessController::class);
            });
    }
}
