<?php declare(strict_types=1);

namespace Kirameki\Framework\Http\Filters;

use Kirameki\Framework\Http\HttpContext;
use Kirameki\Http\HttpResponse;
use Throwable;

interface ExceptionFilter
{
    /**
     * @param HttpContext $context
     * @param Throwable $throwable
     * @return HttpResponse
     */
    public function __invoke(HttpContext $context, Throwable $throwable): HttpResponse;
}
