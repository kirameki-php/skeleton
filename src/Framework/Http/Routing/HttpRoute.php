<?php declare(strict_types=1);

namespace App\Framework\Http\Routing;

use App\Framework\Http\Filters\RouteFilter;
use App\Framework\Http\HttpContext;
use Closure;
use Kirameki\Http\HttpMethod;
use Kirameki\Http\HttpResponse;
use Kirameki\Storage\Path;

abstract class HttpRoute
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
     * @return Closure(HttpContext): HttpResponse
     */
    abstract protected function resolve(HttpContext $context): Closure;

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
        return $filters[$index]($context, $this->generateNextCaller($context, $filters, $index));
    }

    /**
     * @param HttpContext $context
     * @param list<RouteFilter> $filters
     * @param int $index
     * @return Closure(): HttpResponse
     */
    protected function generateNextCaller(HttpContext $context, array $filters, int $index): Closure
    {
        return fn() => ($filters[$index + 1] ?? false)
            ? $this->pipeline($context, $filters, $index + 1)
            : $this->resolve($context)($context);
    }
}
