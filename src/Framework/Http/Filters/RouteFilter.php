<?php declare(strict_types=1);

namespace Kirameki\Framework\Http\Filters;

use Kirameki\Framework\Http\HttpContext;
use Closure;
use Kirameki\Http\HttpResponse;

interface RouteFilter
{
    /**
     * @param HttpContext $context
     * @param Closure(): HttpResponse $next
     * @return HttpResponse
     */
    public function __invoke(HttpContext $context, Closure $next): HttpResponse;
}
