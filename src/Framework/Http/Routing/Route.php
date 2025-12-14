<?php declare(strict_types=1);

namespace App\Framework\Http\Routing;

use Closure;
use Kirameki\Container\Container;
use Kirameki\Http\HttpMethod;
use Kirameki\Http\HttpRequest;
use Kirameki\Http\HttpResponse;
use Kirameki\Storage\Path;

abstract class Route
{
    /**
     * @param HttpMethod $method
     * @param Path $path
     */
    public function __construct(
        public readonly HttpMethod $method,
        public readonly Path $path,
    ) {
    }

    /**
     * @return Closure(RouteContext): HttpResponse
     */
    abstract protected function resolve(): Closure;

    /**
     * @param Container $container
     * @param HttpRequest $request
     * @param list<Closure(RouteContext, Closure): HttpResponse> $routeFilters
     * @return HttpResponse
     */
    public function run(Container $container, HttpRequest $request, array $routeFilters): HttpResponse
    {
        $context = new RouteContext($container, $this, $request);
        return $this->pipeline($context, $routeFilters, 0);
    }

    /**
     * @param RouteContext $context
     * @param list<Closure(RouteContext, Closure): HttpResponse> $filters
     * @param int $index
     * @return HttpResponse
     */
    protected function pipeline(RouteContext $context, array $filters, int $index): HttpResponse
    {
        return $filters[$index]($context, $this->generateNextCaller($context, $filters, $index));
    }

    /**
     * @param RouteContext $context
     * @param list<Closure(RouteContext, Closure): HttpResponse> $filters
     * @param int $index
     * @return Closure(): HttpResponse
     */
    protected function generateNextCaller(RouteContext $context, array $filters, int $index): Closure
    {
        return fn() => ($filters[$index + 1] ?? false)
            ? $this->pipeline($context, $filters, $index + 1)
            : $this->resolve()($context);
    }
}
