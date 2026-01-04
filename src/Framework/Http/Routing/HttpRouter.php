<?php declare(strict_types=1);

namespace Kirameki\Framework\Http\Routing;

use Kirameki\Framework\Foundation\AppScope;
use Kirameki\Framework\Http\Filters\ExceptionFilter;
use Kirameki\Framework\Http\Filters\RouteFilter;
use Kirameki\Framework\Http\HttpContext;
use Kirameki\Container\Container;
use Kirameki\Http\HttpRequest;
use Kirameki\Http\HttpResponse;
use Kirameki\Http\StatusCode;
use Throwable;

class HttpRouter
{
    /**
     * @param Container $container
     * @param HttpRouteTree $tree
     * @param ExceptionFilter $exceptionFilter
     * @param list<RouteFilter> $routeFilters
     */
    public function __construct(
        protected Container $container,
        public readonly HttpRouteTree $tree,
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
        $resource = $this->tree->find($request);
        if ($resource === null) {
            return $this->buildResponse($request, StatusCode::NotFound);
        }

        $route = $resource->getOrNull($request->method);
        if ($route === null) {
            return $this->buildResponse($request, StatusCode::MethodNotAllowed);
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
     * @param int $statusCode
     * @return HttpResponse
     */
    protected function buildResponse(HttpRequest $request, int $statusCode): HttpResponse
    {
        return new HttpResponse($request->version, $statusCode);
    }
}
