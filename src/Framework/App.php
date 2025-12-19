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
use Kirameki\Framework\Foundation\AppState;
use Kirameki\Framework\Foundation\Deployment;
use Kirameki\Framework\Foundation\Initializers\ExceptionInitializer;
use Kirameki\Framework\Foundation\Initializers\LoggerInitializer;
use Kirameki\Framework\Foundation\ServiceInitializer;
use Kirameki\Framework\Http\HttpRunner;
use Kirameki\Framework\Http\Initializers\HttpInitializer;
use Kirameki\Storage\Path;
use Kirameki\System\Env;
use function array_map;
use function file_put_contents;

class App
{
    public AppState $state = AppState::Constructed;

    /**
     * @var Container
     */
    public readonly Container $container;

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
        $this->runInitializers($initializers);

        array_map(fn(AppLifeCycle $lc) => $lc->started($this), $this->lifeCycles);

        $this->markAsReady();
    }

    /**
     * @return void
     */
    public function terminate(): void
    {
        $this->state = AppState::Terminating;

        array_map(fn(AppLifeCycle $lc) => $lc->terminating($this), $this->lifeCycles);
        array_map(fn(AppLifeCycle $lc) => $lc->terminated($this), $this->lifeCycles);

        $this->state = AppState::Terminated;
    }

    /**
     * @param array<string, string> $server
     * @return void
     */
    public function handleHttp(array $server): void
    {
        $this->withScope(
            fn(AppScope $scope) => $this->container->make(HttpRunner::class)->run($scope, $server),
        );
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
            $scope = $this->container->get(AppScope::class);
            $call($scope);
        } finally {
            $this->container->unsetScopedEntries();
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
        $container->scoped(AppScope::class, fn() => new AppScope());
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

    /**
     * @return void
     */
    protected function markAsReady(): void
    {
        file_put_contents('/run/.kirameki', '1');

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
