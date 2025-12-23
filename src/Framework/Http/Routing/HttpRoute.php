<?php declare(strict_types=1);

namespace Kirameki\Framework\Http\Routing;

use Closure;
use Kirameki\Framework\Http\Filters\RouteFilter;
use Kirameki\Framework\Http\HttpContext;
use Kirameki\Http\HttpMethod;
use Kirameki\Http\HttpResponse;

abstract class HttpRoute
{
    /**
     * @param HttpMethod $method
     * @param string $path
     */
    public function __construct(
        public readonly HttpMethod $method,
        public readonly string $path,
    ) {
    }

    /**
     * @return Closure(HttpContext): HttpResponse
     */
    abstract protected function resolve(HttpContext $context): Closure;

    /**
     * @param HttpContext $context
     * @return list<RouteFilter>
     */
    abstract protected function filters(HttpContext $context): array;

    /**
     * @param HttpContext $context
     * @param list<RouteFilter> $filters
     * @return HttpResponse
     */
    public function run(HttpContext $context, array $filters): HttpResponse
    {
        return $this->pipeline($context, $filters, 0);
    }

    /**
     * @param HttpContext $context
     * @param list<RouteFilter> $filters
     * @param int $index
     * @return HttpResponse
     */
    protected function pipeline(HttpContext $context, array $filters, int $index): HttpResponse
    {
        return ($filters[$index] ?? false)
            ? $filters[$index]($context, fn() => $this->pipeline($context, $filters, $index + 1))
            : $this->resolve($context)($context);
    }
}
