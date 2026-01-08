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
        $this->router
            ->healthChecks()
            ->resources('friends', ReadinessController::class, ResourceOptions::default())
            ->namespace('users', function (HttpRouterBuilder $router) {
                $router
                    ->get('/', ReadinessController::class)
                    ->get('/{id|int}', ReadinessController::class)
                    ->get('/{id}', ReadinessController::class);
            });
    }
}
