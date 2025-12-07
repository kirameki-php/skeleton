<?php declare(strict_types=1);

namespace App\Framework;

use App\Framework\LifeCycle\AppLifeCycle;
use App\Framework\LifeCycle\AppScope;
use Kirameki\Clock\ClockInterface;
use Kirameki\Clock\SystemClock;
use Kirameki\Container\Container;

class App
{
    /**
     * @var AppScope|null
     */
    protected ?AppScope $currentScope = null;

    /**
     * @var list<AppLifeCycle>
     */
    protected array $lifeCycles = [];

    /**
     * @param AppEnv $env
     * @param Container $container
     * @param Deployment $deployment
     * @param float $startTimeSeconds
     * @param list<class-string<Initializer>> $initializers
     */
    public function __construct(
        public readonly AppEnv $env,
        protected readonly Deployment $deployment,
        public readonly Container $container,
        protected readonly float $startTimeSeconds,
        protected array $initializers = [],
    ) {
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        $container = $this->container;
        $container->instance(App::class, $this);
        $container->instance(AppEnv::class, $this->env);
        $container->instance(Container::class, $container);
        $container->instance(Deployment::class, $this->deployment);
        $container->instance(ClockInterface::class, new SystemClock());

        foreach ($this->initializers as $initializer) {
            $container->make($initializer)->register($container);
        }

        foreach ($this->lifeCycles as $lifeCycle) {
            $lifeCycle->started($this);
        }
    }

    /**
     * @return void
     */
    public function startScope(): void
    {
        $this->currentScope = new AppScope();
    }

    /**
     * @return void
     */
    public function endScope(): void
    {
        $this->container->unsetScopedEntries();
        $this->currentScope = null;
    }

    /**
     * @return void
     */
    public function terminate(): void
    {
        foreach ($this->lifeCycles as $lifeCycle) {
            $lifeCycle->terminating($this);
        }

        foreach ($this->lifeCycles as $lifeCycle) {
            $lifeCycle->terminated($this);
        }
    }

    /**
     * @param AppLifeCycle $service
     * @return void
     */
    public function addLifeCycleService(AppLifeCycle $service): void
    {
        $this->lifeCycles[] = $service;
    }
}
