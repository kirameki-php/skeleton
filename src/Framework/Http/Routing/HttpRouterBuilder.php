<?php declare(strict_types=1);

namespace App\Framework\Http\Routing;

use App\Framework\Http\Controllers\Controller;
use App\Framework\Http\Filters\ExceptionFilter;
use App\Framework\Http\Filters\RouteFilter;
use Closure;
use Kirameki\Container\Container;
use Kirameki\Http\HttpMethod;
use Kirameki\Http\HttpRequest;
use Kirameki\Http\HttpResponse;
use Kirameki\Storage\Path;

class HttpRouterBuilder
{
    /**
     * @var list<HttpRoute>
     */
    protected array $routes = [];

    /**
     * @param Container $container
     * @param ExceptionFilter|null $exceptionFilter
     * @param array $routeFilters
     */
    public function __construct(
        protected Container $container,
        protected ?ExceptionFilter $exceptionFilter = null,
        protected array $routeFilters = [],
    ) {
    }

    /**
     * @param HttpMethod $method
     * @param Path $path
     * @param class-string<Controller>|Closure(HttpRequest): HttpResponse $controller
     * @return $this
     */
    public function addRoute(HttpMethod $method, Path $path, string|Closure $controller): static
    {
        $this->routes[] = ($controller instanceof Closure)
            ? new CallbackHttpRoute($method, $path, $controller)
            : new ControllerHttpRoute($method, $path, $controller);
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
            $this->routes,
            $this->exceptionFilter,
            $this->routeFilters,
        );
    }
}
