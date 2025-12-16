<?php declare(strict_types=1);

namespace App\Framework\Http\Filters;

use App\Framework\Http\HttpContext;
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
