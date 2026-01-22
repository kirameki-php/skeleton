<?php declare(strict_types=1);

namespace Kirameki\Framework\Exception;

use Kirameki\Framework\Logging\LoggerBuilder;

trait BuildsExceptionHandler
{
    /**
     * @template T of object
     * @param class-string<T> $id
     * @return T
     */
    abstract protected function resolve(string $id): object;

    /**
     * @var ExceptionHandlerBuilder
     */
    protected ExceptionHandlerBuilder $exceptionHandler {
        get => $this->exceptionHandler ??= $this->resolve(ExceptionHandlerBuilder::class);
    }
}
