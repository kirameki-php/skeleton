<?php declare(strict_types=1);

namespace Kirameki\App\Initializers;

use Kirameki\Framework\Http\Controllers\Health\ReadinessController;
use Kirameki\Framework\Http\Initializers\RoutesInitializerBase;
use Kirameki\Framework\Http\Routing\HttpRouter;
use Kirameki\Framework\Http\Routing\HttpRouterBuilder;
use Override;

class RoutesInitializer extends RoutesInitializerBase
{
    /**
     * @inheritDoc
     */
    #[Override]
    protected function setupRoutes(HttpRouterBuilder $router): HttpRouter
    {
        return $router
            ->get('/readyz', ReadinessController::class)
            ->get('/users/{id|int}', ReadinessController::class)
            ->get('/users/{id}', ReadinessController::class)
            ->build();
    }
}
