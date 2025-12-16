<?php declare(strict_types=1);

namespace App\Framework\Http\Filters;

use App\Framework\Http\HttpContext;
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
