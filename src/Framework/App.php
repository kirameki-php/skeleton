<?php declare(strict_types=1);

namespace Kirameki\Framework;

use Closure;
use Kirameki\Clock\ClockInterface;
use Kirameki\Clock\SystemClock;
use Kirameki\Container\Container;
use Kirameki\Event\EventDispatcher;
use Kirameki\Framework\Crypto\NanoIdGenerator;
use Kirameki\Framework\Foundation\AppEnv;
use Kirameki\Framework\Foundation\AppLifeCycle;
use Kirameki\Framework\Foundation\AppScope;
use Kirameki\Framework\Foundation\Deployment;
use Kirameki\Framework\Foundation\ServiceInitializer;
use Kirameki\Framework\Http\HealthCheck;
use Kirameki\Framework\Http\HttpRunner;
use Kirameki\Storage\Path;
use Kirameki\System\Env;
use function touch;

class App
{
    /**
     * @var string
     */
    public string $threadId = '';

    /**
     * @var list<AppLifeCycle>
     */
    protected array $lifeCycles = [];

    /**
     * @var float
     */
    protected readonly float $startTimeSeconds;

    /**
     * @param Path $path
     * @param Container $container
     */
    public function __construct(
        public readonly Path $path,
        public readonly Container $container = new Container(),
    ) {
        $this->startTimeSeconds = microtime(true);
    }

    /**
     * @param array<class-string<ServiceInitializer>> $initializers
     * @return void
     */
    public function boot(array $initializers): void
    {
        $this->injectEssentialServices();
        $this->generateThreadId();
        $this->runInitializers($initializers);

        foreach ($this->lifeCycles as $lifeCycle) {
            $lifeCycle->started($this);
        }

        $this->markAsReady();
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
     * @param array<string, string> $server
     * @return void
     */
    public function handleHttp(array $server): void
    {
        $httpRunner = $this->container->make(HttpRunner::class);
        $this->withScope(static fn(AppScope $scope) => $httpRunner->run($scope, $server));
    }

    /**
     * @param AppLifeCycle $service
     * @return void
     */
    public function addLifeCycleService(AppLifeCycle $service): void
    {
        $this->lifeCycles[] = $service;
    }

    /**
     * @param Closure(AppScope): mixed $call
     * @return void
     */
    protected function withScope(Closure $call): void
    {
        try {
            $call($this->container->get(AppScope::class));
        } finally {
            $this->container->unsetScopedInstances();
        }
    }

    /**
     * @return void
     */
    protected function injectEssentialServices(): void
    {
        $container = $this->container;
        $container->instance(Container::class, $container);
        $container->instance(App::class, $this);
        $container->instance(AppEnv::class, $this->makeAppEnv(...));
        $container->instance(Deployment::class, $this->makeDeployment(...));
        $container->instance(ClockInterface::class, new SystemClock());
        $container->instance(EventDispatcher::class, new EventDispatcher());
        $container->instance(NanoIdGenerator::class, new NanoIdGenerator());
        $container->scoped(AppScope::class, fn() => new AppScope());
        $container->singleton(HttpRunner::class);
    }

    /**
     * @return void
     */
    protected function generateThreadId(): void
    {
        $this->threadId = $this->container->get(NanoIdGenerator::class)->generate();
    }

    /**
     * @param array<class-string<ServiceInitializer>> $userInitializers
     * @return void
     */
    protected function runInitializers(array $userInitializers): void
    {
        $container = $this->container;

        foreach ($userInitializers as $initializer) {
            $container->make($initializer)->register($container);
        }
    }

    protected function invokeLifeCycleStartedMethods(): void
    {
        foreach ($this->lifeCycles as $lifeCycle) {
            $lifeCycle->started($this);
        }
    }

    /**
     * @return void
     */
    protected function markAsReady(): void
    {
        touch(HealthCheck::READINESS_FILE);
    }

    /**
     * @return AppEnv
     */
    protected function makeAppEnv(): AppEnv
    {
        return new AppEnv(
            $this->path,
            Env::getBoolOrNull('DEVELOP_MODE') ?? false,
            Env::getBoolOrNull('TEST_MODE') ?? false,
        );
    }

    /**
     * @return Deployment
     */
    protected function makeDeployment(): Deployment
    {
        return new Deployment(
            Env::getString('NAMESPACE'),
            Env::getStringOrNull('DEPLOYER') ?? 'unknown',
            Env::getStringOrNull('REVISION') ?? 'unknown',
            Env::getFloatOrNull('DEPLOY_TIME') ?? 0.0,
        );
    }
}
