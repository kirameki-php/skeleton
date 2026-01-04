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
use function array_pop;
use function count;
use function explode;
use function implode;
use function trim;

class HttpRouterBuilder
{
    /**
     * @param Container $container
     * @param ExceptionFilter|null $exceptionFilter
     * @param list<RouteFilter> $routeFilters
     * @param HttpRouteTree $tree
     * @param list<string> $namespaces
     */
    public function __construct(
        protected Container $container,
        protected ?ExceptionFilter $exceptionFilter = null,
        protected array $routeFilters = [],
        protected HttpRouteTree $tree = new HttpRouteTree(),
        protected array $namespaces = [],
    ) {
    }

    /**
     * @param string $prefix
     * @param Closure(HttpRouterBuilder): void $call
     * @return $this
     */
    public function namespace(string $prefix, Closure $call): static
    {
        try {
            $this->namespaces[] = $prefix;
            $call($this);
            return $this;
        }
        finally {
            array_pop($this->namespaces);
        }
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
     * @param string $path
     * @param class-string<Controller> $controller
     * @param ResourceOptions|null $options
     * @return $this
     */
    public function resources(string $path, string $controller, ?ResourceOptions $options = null): static
    {
        $options ??= ResourceOptions::default();

        if ($options->viewable) {
            $this->addRoute(HttpMethod::GET, $path, $controller, 'index');
            $this->addRoute(HttpMethod::GET, "{$path}/{id}", $controller, 'show');
        }

        if ($options->creatable) {
            if ($options->form) {
                $this->addRoute(HttpMethod::GET, "{$path}/new", $controller, 'new');
            }
            $this->addRoute(HttpMethod::POST, $path, $controller, 'create');
        }

        if ($options->editable) {
            if ($options->form) {
                $this->addRoute(HttpMethod::GET, "{$path}/{id}/edit", $controller, 'edit');
            }
            $this->addRoute(HttpMethod::PUT, "{$path}/{id}", $controller, 'update');
            $this->addRoute(HttpMethod::PATCH, "{$path}/{id}", $controller, 'update');
        }

        if ($options->deletable) {
            $this->addRoute(HttpMethod::DELETE, "{$path}/{id}", $controller, 'delete');
        }

        return $this;
    }

    /**
     * @param HttpMethod $method
     * @param string $path
     * @param class-string<Controller>|Closure(HttpRequest): HttpResponse $controller
     * @param string|null $action
     * @return $this
     */
    public function addRoute(HttpMethod $method, string $path, string|Closure $controller, ?string $action = null): static
    {
        $path = trim($path, '/');
        $path = ($path !== '')
            ? implode('/', [...$this->namespaces, $path])
            : implode('/', $this->namespaces);

        $route = ($controller instanceof Closure)
            ? new CallbackHttpRoute($method, $path, $controller)
            : new ControllerHttpRoute($method, $path, $controller, $action);

        $this->tree->add(explode('/', $route->path), $route);

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
