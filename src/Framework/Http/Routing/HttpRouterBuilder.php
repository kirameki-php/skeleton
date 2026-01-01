<?php declare(strict_types=1);

namespace Kirameki\Framework\Http\Routing;

use Closure;
use Kirameki\Framework\Http\Controllers\Controller;
use Kirameki\Framework\Http\Controllers\Health\LivenessController;
use Kirameki\Framework\Http\Controllers\Health\ReadinessController;
use Kirameki\Framework\Http\Filters\DefaultExceptionFilter;
use Kirameki\Framework\Http\Filters\ExceptionFilter;
use Kirameki\Framework\Http\Filters\RouteFilter;
use Kirameki\Container\Container;
use Kirameki\Http\HttpMethod;
use Kirameki\Http\HttpRequest;
use Kirameki\Http\HttpResponse;

class HttpRouterBuilder
{
    /**
     * @param Container $container
     * @param ExceptionFilter|null $exceptionFilter
     * @param list<RouteFilter> $routeFilters
     * @param HttpRouteTree $tree
     */
    public function __construct(
        protected Container $container,
        protected ?ExceptionFilter $exceptionFilter = null,
        protected array $routeFilters = [],
        protected HttpRouteTree $tree = new HttpRouteTree(),
    ) {
    }

    /**
     * @return $this
     */
    public function get(string $path, string|Closure $controller): static
    {
        return $this->addRoute(HttpMethod::GET, $path, $controller);
    }

    /**
     * @return $this
     */
    public function post(string $path, string|Closure $controller): static
    {
        return $this->addRoute(HttpMethod::POST, $path, $controller);
    }

    /**
     * @return $this
     */
    public function put(string $path, string|Closure $controller): static
    {
        return $this->addRoute(HttpMethod::PUT, $path, $controller);
    }

    /**
     * @return $this
     */
    public function delete(string $path, string|Closure $controller): static
    {
        return $this->addRoute(HttpMethod::DELETE, $path, $controller);
    }

    /**
     * @return $this
     */
    public function healthChecks(string $readinessPath = '/readyz', string $livenessPath = 'livez'): static
    {
        return $this
            ->get($readinessPath, ReadinessController::class)
            ->get($livenessPath, LivenessController::class);
    }

    /**
     * @param HttpMethod $method
     * @param string $path
     * @param class-string<Controller>|Closure(HttpRequest): HttpResponse $controller
     * @return $this
     */
    public function addRoute(HttpMethod $method, string $path, string|Closure $controller): static
    {
        $route = ($controller instanceof Closure)
            ? new CallbackHttpRoute($method, $path, $controller)
            : new ControllerHttpRoute($method, $path, $controller);

        $segments = explode('/', trim($route->path, '/'));
        $this->tree->add($segments, $route);

        return $this;
    }

    /**
     * @param ExceptionFilter $filter
     * @return $this
     */
    public function setExceptionFilter(ExceptionFilter $filter): static
    {
        $this->exceptionFilter = $filter;
        return $this;
    }

    /**
     * @param RouteFilter $filter
     * @return $this
     */
    public function addRouteFilter(RouteFilter $filter): static
    {
        $this->routeFilters[] = $filter;
        return $this;
    }

    /**
     * @return HttpRouter
     */
    public function build(): HttpRouter
    {
        return new HttpRouter(
            $this->container,
            $this->tree,
            $this->exceptionFilter ?? new DefaultExceptionFilter(),
            $this->routeFilters,
        );
    }
}
