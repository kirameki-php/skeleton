<?php declare(strict_types=1);

namespace Kirameki\Framework;

use Closure;
use Kirameki\Clock\ClockInterface;
use Kirameki\Clock\SystemClock;
use Kirameki\Container\Container;
use Kirameki\Event\EventDispatcher;
use Kirameki\Framework\Cli\CommandRunner;
use Kirameki\Framework\Crypto\NanoIdGenerator;
use Kirameki\Framework\Foundation\AppEnv;
use Kirameki\Framework\Foundation\AppLifeCycle;
use Kirameki\Framework\Foundation\AppScope;
use Kirameki\Framework\Foundation\Deployment;
use Kirameki\Framework\Foundation\ServiceInitializer;
use Kirameki\Framework\Http\HttpServer;
use Kirameki\Storage\Path;
use Kirameki\System\Env;

class App
{
    /**
     * @var string
     */
    public string $runId = '';

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
        $this->generateRunId();
        $this->runInitializers($initializers);

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
     * @param array<string, mixed> $info
     * @return void
     */
    public function handleHttp(array $info): void
    {
        $server = $this->container->get(HttpServer::class);
        $this->withScope(static fn(AppScope $scope) => $server->run($scope, $info));
    }

    /**
     * @param string $name
     * @param list<string> $parameters
     * @return int
     */
    public function runCommand(array $args): int
    {
        $runner = $this->container->make(CommandRunner::class);
        return $this->withScope(static fn() => $runner->runFromArgs($args));
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
     * @template TReturn of mixed
     * @param Closure(AppScope): TReturn $call
     * @return TReturn
     */
    protected function withScope(Closure $call): mixed
    {
        try {
            return $call($this->container->get(AppScope::class));
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
        $container->singleton(HttpServer::class);
    }

    /**
     * @return void
     */
    protected function generateRunId(): void
    {
        $this->runId = $this->container->get(NanoIdGenerator::class)->generate();
    }

    /**
     * @param array<class-string<ServiceInitializer>> $initializers
     * @return void
     */
    protected function runInitializers(array $initializers): void
    {
        $container = $this->container;
        foreach ($initializers as $initializer) {
            $container->make($initializer)->register($container);
        }
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
