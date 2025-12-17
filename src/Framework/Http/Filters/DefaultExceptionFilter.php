<?php declare(strict_types=1);

namespace Kirameki\Framework\Http\Filters;

use Kirameki\Framework\Http\HttpContext;
use Kirameki\Http\HttpResponse;
use Kirameki\Http\HttpResponseBody;
use Kirameki\Http\StatusCode;
use Throwable;

class DefaultExceptionFilter implements ExceptionFilter
{
    public function __invoke(HttpContext $context, Throwable $throwable): HttpResponse
    {
        return new HttpResponse(
            $context->request->version,
            StatusCode::InternalServerError,
            body: new HttpResponseBody((string) $throwable),
        );
    }
}
