<?php declare(strict_types=1);

namespace App\Framework\Http\Routing;

use Kirameki\Container\Container;
use Kirameki\Http\HttpRequest;

class RouteContext
{
    /**
     * @param Container $container
     * @param Route $route
     * @param HttpRequest $request
     */
    public function __construct(
        public readonly Container $container,
        public readonly Route $route,
        public readonly HttpRequest $request,
    ) {
    }
}
