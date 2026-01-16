<?php declare(strict_types=1);

namespace Kirameki\Framework;

use Kirameki\Container\Container;
use Kirameki\Framework\Foundation\AppEnv;
use Kirameki\Framework\Foundation\AppLifeCycle;
use Kirameki\Framework\Foundation\AppRunner;
use Kirameki\Framework\Foundation\Deployment;
use Kirameki\Framework\Http\HttpServer;
use Kirameki\Storage\Path;

class App
{
    /**
     * @param Path $path
     * @param Container $container
     * @param AppEnv $env
     * @param Deployment $deployment
     * @param string $runId
     * @param class-string<AppRunner> $runnerClass
     * @param list<AppLifeCycle> $lifeCycles
     * @param float $startTimeSeconds
     */
    public function __construct(
        public readonly Path $path,
        public readonly Container $container,
        public readonly AppEnv $env,
        public readonly Deployment $deployment,
        public readonly string $runId,
        public readonly string $runnerClass,
        protected array $lifeCycles = [],
        public readonly float $startTimeSeconds = 0.0,
    ) {
    }

    /**
     * @return int
     */
    public function run(): int
    {
        $this->boot();

        $runner = $this->container->make($this->runnerClass);
        $exitCode = $runner->run();

        $this->terminate($exitCode);

        return $exitCode;
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
     * @param int $exitCode
     * @return void
     */
    protected function terminate(int $exitCode): void
    {
        foreach ($this->lifeCycles as $lifeCycle) {
            $lifeCycle->terminating($this, $exitCode);
        }

        foreach ($this->lifeCycles as $lifeCycle) {
            $lifeCycle->terminated($this, $exitCode);
        }
    }
}
