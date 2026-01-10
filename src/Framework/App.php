<?php declare(strict_types=1);

namespace Kirameki\Framework;

use Closure;
use Kirameki\Clock\ClockInterface;
use Kirameki\Clock\SystemClock;
use Kirameki\Container\Container;
use Kirameki\Event\EventDispatcher;
use Kirameki\Framework\Foundation\AppEnv;
use Kirameki\Framework\Foundation\AppLifeCycle;
use Kirameki\Framework\Foundation\AppScope;
use Kirameki\Framework\Foundation\Deployment;
use Kirameki\Framework\Foundation\ServiceInitializer;
use Kirameki\Storage\Path;

abstract class App
{
    /**
     * @param Path $path
     * @param Container $container
     * @param AppEnv $env
     * @param Deployment $deployment
     * @param string $runId
     * @param list<class-string<ServiceInitializer>> $initializers
     * @param list<AppLifeCycle> $lifeCycles
     * @param float $startTimeSeconds
     */
    public function __construct(
        public readonly Path $path,
        public readonly Container $container,
        public readonly AppEnv $env = new AppEnv(),
        public readonly Deployment $deployment = new Deployment(),
        public readonly string $runId = '',
        protected array $initializers = [],
        protected array $lifeCycles = [],
        public readonly float $startTimeSeconds = 0.0,
    ) {
    }

    /**
     * @return void
     */
    protected function boot(): void
    {
        foreach ($this->lifeCycles as $lifeCycle) {
            $lifeCycle->started($this);
        }
    }

    /**
     * @return void
     */
    protected function terminate(): void
    {
        foreach ($this->lifeCycles as $lifeCycle) {
            $lifeCycle->terminating($this);
        }

        foreach ($this->lifeCycles as $lifeCycle) {
            $lifeCycle->terminated($this);
        }
    }

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
