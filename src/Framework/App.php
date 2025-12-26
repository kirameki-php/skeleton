<?php declare(strict_types=1);

namespace Kirameki\Framework;

use Closure;
use Kirameki\App\Initializers\LoggerInitializer;
use Kirameki\Clock\ClockInterface;
use Kirameki\Clock\SystemClock;
use Kirameki\Container\Container;
use Kirameki\Event\EventDispatcher;
use Kirameki\Framework\Crypto\NanoIdGenerator;
use Kirameki\Framework\Foundation\AppEnv;
use Kirameki\Framework\Foundation\AppLifeCycle;
use Kirameki\Framework\Foundation\AppScope;
use Kirameki\Framework\Foundation\AppState;
use Kirameki\Framework\Foundation\Deployment;
use Kirameki\Framework\Foundation\Initializers\ExceptionInitializer;
use Kirameki\Framework\Foundation\ServiceInitializer;
use Kirameki\Framework\Http\HealthCheck;
use Kirameki\Framework\Http\HttpRunner;
use Kirameki\Framework\Http\Initializers\HttpInitializer;
use Kirameki\Storage\Path;
use Kirameki\System\Env;
use function touch;

class App
{
    /**
     * @var AppState
     */
    public AppState $state = AppState::Constructed;

    /**
     * @var Container
     */
    public readonly Container $container;

    /**
     * @var string
     */
    public string $threadId = '';

    /**
     * @var list<class-string<ServiceInitializer>>
     */
    protected array $initializers = [
        LoggerInitializer::class,
        ExceptionInitializer::class,
        HttpInitializer::class,
    ];

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
     */
    public function __construct(protected Path $path)
    {
        $this->container = new Container();
        $this->startTimeSeconds = microtime(true);
    }

    /**
     * @param array<class-string<ServiceInitializer>> $initializers
     * @return void
     */
    public function boot(array $initializers): void
    {
        $this->state = AppState::Booting;

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
        $this->state = AppState::Terminating;

        foreach ($this->lifeCycles as $lifeCycle) {
            $lifeCycle->terminating($this);
        }

        foreach ($this->lifeCycles as $lifeCycle) {
            $lifeCycle->terminated($this);
        }

        $this->state = AppState::Terminated;
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

        foreach ($this->initializers as $initializer) {
            $container->make($initializer)->register($container);
        }

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

        $this->state = AppState::Running;
    }

    /**
     * @return AppEnv
     */
    protected function makeAppEnv(): AppEnv
    {
        $isDevelopment = Env::getBoolOrNull('APP_DEVELOP_MODE') ?? false;
        $inTestMode = Env::getBoolOrNull('APP_TEST_MODE') ?? false;

        return new AppEnv($this->path, $isDevelopment, $inTestMode);
    }

    /**
     * @return Deployment
     */
    protected function makeDeployment(): Deployment
    {
        $namespace = Env::getString('APP_NAMESPACE');

        return new Deployment($namespace);
    }
}
