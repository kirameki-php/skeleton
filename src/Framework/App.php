<?php declare(strict_types=1);

namespace App\Framework;

use App\Framework\Foundation\AppEnv;
use App\Framework\Foundation\AppLifeCycle;
use App\Framework\Foundation\AppScope;
use App\Framework\Foundation\Deployment;
use App\Framework\Foundation\Initializers\ExceptionInitializer;
use App\Framework\Foundation\Initializers\LoggerInitializer;
use App\Framework\Foundation\ServiceInitializer;
use App\Framework\Http\HttpInitializer;
use App\Framework\Http\HttpRunner;
use Closure;
use Kirameki\Clock\ClockInterface;
use Kirameki\Clock\SystemClock;
use Kirameki\Container\Container;
use Kirameki\Event\EventDispatcher;
use Kirameki\Storage\Path;
use Kirameki\System\Env;

class App
{
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
     * @return void
     */
    public function boot(): void
    {
        $this->injectEssentialServices();
        $this->runInitializers();

        foreach ($this->lifeCycles as $lifeCycle) {
            $lifeCycle->started($this);
        }
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
        $this->withScope(function () use ($server): void {
            $this->container->make(HttpRunner::class)->run($server);
        });
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
     * @param Closure(): mixed $call
     * @return void
     */
    protected function withScope(Closure $call): void
    {
        $scope = new AppScope();
        try {
            $this->container->scoped(AppScope::class, fn() => $scope);
            $call();
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
    }

    /**
     * @return void
     */
    protected function runInitializers(): void
    {
        $container = $this->container;
        foreach ($this->initializers as $initializer) {
            $container->make($initializer)->register($container);
        }
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
