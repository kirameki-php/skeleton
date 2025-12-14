<?php declare(strict_types=1);

namespace App\Framework;

use App\Framework\Foundation\AppEnv;
use App\Framework\Foundation\AppLifeCycle;
use App\Framework\Foundation\AppRunner;
use App\Framework\Foundation\AppScope;
use App\Framework\Foundation\Deployment;
use App\Framework\Foundation\Initializers\ExceptionInitializer;
use App\Framework\Foundation\Initializers\LoggerInitializer;
use App\Framework\Foundation\ServiceInitializer;
use Kirameki\Clock\ClockInterface;
use Kirameki\Clock\SystemClock;
use Kirameki\Container\Container;
use Kirameki\Storage\Path;
use Kirameki\System\Env;
use RuntimeException;

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
    ];

    /**
     * @var AppScope|null
     */
    protected ?AppScope $currentScope = null;

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
    public function handle(array $server): void
    {
        try {
            $this->startScope();
            $this->container->make(AppRunner::class)->run();
        }
        finally {
            $this->endScope();
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

    /**
     * @return void
     */
    protected function startScope(): void
    {
        $this->currentScope = new AppScope();
    }

    /**
     * @return void
     */
    protected function endScope(): void
    {
        if ($this->currentScope === null) {
            throw new RuntimeException('No active scope to end.');
        }
        $this->container->unsetScopedEntries();
        $this->currentScope = null;
    }

    /**
     * @return void
     */
    protected function injectEssentialServices(): void
    {
        $container = $this->container;
        $container->instance(App::class, $this);
        $container->instance(Container::class, $container);
        $container->instance(ClockInterface::class, new SystemClock());
        $container->instance(AppEnv::class, $this->makeAppEnv(...));
        $container->instance(Deployment::class, $this->makeDeployment(...));
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
