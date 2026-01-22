<?php declare(strict_types=1);

namespace Kirameki\Framework;

use Kirameki\Container\Container;
use Kirameki\Exceptions\InvalidArgumentException;
use Kirameki\Framework\Console\CommandRunner;
use Kirameki\Framework\Foundation\AppEnv;
use Kirameki\Framework\Foundation\AppLifeCycle;
use Kirameki\Framework\Foundation\AppRunner;
use Kirameki\Framework\Foundation\Deployment;
use Kirameki\Framework\Http\HttpServer;
use Kirameki\Storage\Path;

class App
{
    /**
     * @var AppEnv
     */
    public AppEnv $env {
        get => $this->env ??= $this->container->get(AppEnv::class);
    }

    public Deployment $deployment {
        get => $this->deployment ??= $this->container->get(Deployment::class);
    }

    /**
     * @var class-string<AppRunner>|null
     */
    public ?string $runnerClass = null;

    /**
     * @param Path $path
     * @param Container $container
     * @param string $runId
     * @param list<AppLifeCycle> $lifeCycles
     * @param float $startTimeSeconds
     */
    public function __construct(
        public readonly Path $path,
        public readonly Container $container,
        public readonly string $runId,
        protected array $lifeCycles = [],
        public readonly float $startTimeSeconds = 0.0,
    ) {
    }

    /**
     * @param class-string<AppRunner> $runner
     * @return $this
     */
    public function useRunner(string $runner): static
    {
        if ($this->runnerClass !== null) {
            throw new InvalidArgumentException("Runner has already been set to {$this->runnerClass}");
        }

        $this->runnerClass = $runner;
        return $this;
    }

    /**
     * @return $this
     */
    public function useHttpRunner(): static
    {
        return $this->useRunner(HttpServer::class);
    }

    /**
     * @return $this
     */
    public function useCommandRunner(): static
    {
        return $this->useRunner(CommandRunner::class);
    }

    /**
     * @return int
     */
    public function run(): int
    {
        $this->boot();

        if ($this->runnerClass === null) {
            throw new InvalidArgumentException('Runner class has not been set.');
        }

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
