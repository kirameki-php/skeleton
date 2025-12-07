<?php declare(strict_types=1);

namespace App\Framework\Http\Routing;

use Kirameki\Container\Container;
use Kirameki\Exceptions\RuntimeException;
use Kirameki\Http\HttpRequest;
use Kirameki\Http\HttpResponse;

class Router
{
    /**
     * @param Container $container
     * @param list<Route> $routes
     */
    public function __construct(
        protected Container $container,
        protected array $routes,
    ) {
    }

    /**
     * @param HttpRequest $request
     * @return HttpResponse
     */
    public function dispatch(HttpRequest $request): HttpResponse
    {
        $route = $this->findMatchingRoute($request);

        if ($route === null) {
            throw new RuntimeException('No matching route found.');
        }

        return $route->run($this->container, $request);
    }

    /**
     * @param HttpRequest $request
     * @return Route|null
     */
    protected function findMatchingRoute(HttpRequest $request): ?Route
    {

    }
}
