<?php declare(strict_types=1);

namespace Kirameki\Framework\Foundation;

use Closure;
use Kirameki\Container\Container;

abstract class AppRunner
{
    /**
     * @param Container $container
     */
    public function __construct(
        protected readonly Container $container,
    ) {
    }

    /**
     * Runs the application.
     *
     * @return int Exit code.
     */
    abstract public function run(): int;

    /**
     * @template TReturn of mixed
     * @param Closure(AppScope): TReturn $call
     * @return TReturn
     */
    protected function withScope(Closure $call): mixed
    {
        try {
            return $call($this->container->get(AppScope::class));
        } finally {
            $this->container->clearScoped();
        }
    }
}
