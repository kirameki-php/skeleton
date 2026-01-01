<?php declare(strict_types=1);

namespace Kirameki\Framework\Http\Exceptions;

use Kirameki\Http\StatusCode;
use Throwable;

class BadRequestException extends HttpException
{
    public function __construct(
        $message = "",
        ?iterable $context = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct(StatusCode::BadRequest, $message, $context, $previous);
    }
}
