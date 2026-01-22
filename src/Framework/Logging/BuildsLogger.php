<?php declare(strict_types=1);

namespace Kirameki\Framework\Logging;

trait BuildsLogger
{
    /**
     * @template T of object
     * @param class-string<T> $id
     * @return T
     */
    abstract protected function resolve(string $id): object;

    /**
     * @var LoggerBuilder
     */
    protected LoggerBuilder $logger {
        get => $this->logger ??= $this->resolve(LoggerBuilder::class);
    }
}
