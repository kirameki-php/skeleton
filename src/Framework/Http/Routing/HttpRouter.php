<?php declare(strict_types=1);

namespace Kirameki\Framework\Http\Routing;

use Kirameki\Framework\Foundation\AppScope;
use Kirameki\Framework\Http\Filters\ExceptionFilter;
use Kirameki\Framework\Http\Filters\RouteFilter;
use Kirameki\Framework\Http\HttpContext;
use Kirameki\Container\Container;
use Kirameki\Http\HttpRequest;
use Kirameki\Http\HttpResponse;
use Throwable;

class HttpRouter
{
    /**
     * @param Container $container
     * @param list<HttpRoute> $routes
     * @param ExceptionFilter $exceptionFilter
     * @param list<RouteFilter> $routeFilters
     */
    public function __construct(
        protected Container $container,
        protected array $routes,
        protected ExceptionFilter $exceptionFilter,
        protected array $routeFilters = [],
    ) {
    }

    /**
     * @param AppScope $scope
     * @param HttpRequest $request
     * @return HttpResponse
     */
    public function dispatch(AppScope $scope, HttpRequest $request): HttpResponse
    {
        $route = $this->findMatchingRoute($request);
        if ($route === null) {
            return new HttpResponse($request->version, 404);
        }

        $context = new HttpContext($this->container, $scope, $request, $route);

        try {
            return $route->run($context, $this->routeFilters);
        }
        catch (Throwable $e) {
            return ($this->exceptionFilter)($context, $e);
        }
    }

    /**
     * @param HttpRequest $request
     * @return HttpRoute|null
     */
    protected function findMatchingRoute(HttpRequest $request): ?HttpRoute
    {
        foreach ($this->routes as $route) {
            return $route;
        }
        return null;
    }
}
