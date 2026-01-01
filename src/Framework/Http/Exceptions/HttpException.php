<?php declare(strict_types=1);

namespace Kirameki\Framework\Http\Exceptions;

use Kirameki\Exceptions\RuntimeException;
use Throwable;

class HttpException extends RuntimeException
{
    /**
     * @param int $statusCode
     * @param string $message
     * @param iterable<string, mixed>|null $context
     * @param Throwable|null $previous
     */
    public function __construct(
        public readonly int $statusCode,
        string $message = '',
        ?iterable $context = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $context, 0, $previous);
    }
}
