<?php declare(strict_types=1);

namespace Kirameki\App\Initializers;

use Kirameki\Framework\Http\Controllers\Health\ReadinessController;
use Kirameki\Framework\Http\Initializers\RoutesInitializerAbstract;
use Kirameki\Framework\Http\Routing\HttpRouterBuilder;

class RoutesInitializer extends RoutesInitializerAbstract
{
    protected function setup(HttpRouterBuilder $router): void
    {
        $router
            ->get('/readyz', ReadinessController::class)
            ->get('/users/{id|int}', ReadinessController::class)
            ->get('/users/{id}', ReadinessController::class);
    }
}
