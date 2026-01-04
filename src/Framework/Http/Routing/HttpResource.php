<?php declare(strict_types=1);

namespace Kirameki\Framework\Http\Routing;

use Kirameki\Exceptions\RuntimeException;
use Kirameki\Http\HttpMethod;

class HttpResource
{
    /**
     * @param array<string, HttpRoute> $routes
     */
    public function __construct(
        public array $routes = [],
    ) {
    }

    /**
     * @param HttpMethod $method
     * @return bool
     */
    public function has(HttpMethod $method): bool
    {
        return isset($this->routes[$method->value]);
    }

    /**
     * @param HttpMethod $method
     * @return HttpRoute|null
     */
    public function getOrNull(HttpMethod $method): ?HttpRoute
    {
        return $this->routes[$method->value] ?? null;
    }

    /**
     * @param HttpRoute $route
     * @return void
     */
    public function add(HttpRoute $route): void
    {
        if ($this->has($route->method)) {
            throw new RuntimeException("Route for [{$route->method->value}] already exists.");
        }
        $this->routes[$route->method->value] = $route;
    }
}
