<?php declare(strict_types=1);

namespace Kirameki\Framework;

use Kirameki\Clock\ClockInterface;
use Kirameki\Clock\SystemClock;
use Kirameki\Container\Container;
use Kirameki\Container\ContainerBuilder;
use Kirameki\Event\EventDispatcher;
use Kirameki\Framework\Console\ConsoleInitializer;
use Kirameki\Framework\Crypto\NanoIdGenerator;
use Kirameki\Framework\Exception\ExceptionHandler;
use Kirameki\Framework\Exception\ExceptionHandlerBuilder;
use Kirameki\Framework\Foundation\AppEnv;
use Kirameki\Framework\Foundation\AppLifeCycle;
use Kirameki\Framework\Foundation\AppScope;
use Kirameki\Framework\Foundation\Deployment;
use Kirameki\Framework\Foundation\ServiceInitializer;
use Kirameki\Framework\Http\WebInitializer;
use Kirameki\Framework\Logging\Logger;
use Kirameki\Framework\Logging\LoggerBuilder;
use Kirameki\Storage\Path;
use Kirameki\System\Env;
use function microtime;

class AppBuilder
{
    /**
     * @var AppEnv
     */
    public readonly AppEnv $env;

    /**
     * @var Deployment
     */
    public readonly Deployment $deployment;

    /**
     * @var string
     */
    public readonly string $runId;

    /**
     * @var float
     */
    public readonly float $startTimeSeconds;

    /**
     * @param Path $path
     * @param ContainerBuilder $containerBuilder
     * @param list<class-string<ServiceInitializer>> $initializers
     * @param list<AppLifeCycle> $lifeCycles
     * @param NanoIdGenerator $idGenerator
     */
    public function __construct(
        public readonly Path $path,
        protected ContainerBuilder $containerBuilder = new ContainerBuilder(),
        protected array $initializers = [],
        protected array $lifeCycles = [],
        protected readonly NanoIdGenerator $idGenerator = new NanoIdGenerator(),
    ) {
        $this->env = $this->instantiateAppEnv();
        $this->deployment = $this->instantiateDeployment();
        $this->runId = $this->generateRunId();
        $this->startTimeSeconds = microtime(true);

        $this->addInitializer(ConsoleInitializer::class);
        $this->addInitializer(WebInitializer::class);
    }

    /**
     * @return ConsoleApp
     */
    public function buildForConsole(): ConsoleApp
    {
        return new ConsoleApp(
            $this->path,
            $this->prepareContainer(),
            $this->env,
            $this->deployment,
            $this->runId,
            $this->initializers,
            $this->lifeCycles,
            $this->startTimeSeconds,
        );
    }

    /**
     * @return WebApp
     */
    public function buildForWeb(): WebApp
    {
        return new WebApp(
            $this->path,
            $this->prepareContainer(),
            $this->env,
            $this->deployment,
            $this->runId,
            $this->initializers,
            $this->lifeCycles,
            $this->startTimeSeconds,
        );
    }

    protected function prepareContainer(): Container
    {
        $builder = $this->containerBuilder;
        $this->registerServices($builder);

        $container = $this->containerBuilder->build();
        $this->initializeServices($container);
        return $container;
    }

    /**
     * @param ContainerBuilder $builder
     * @return void
     */
    protected function registerServices(ContainerBuilder $builder): void
    {
        $builder->instance(App::class, $this);
        $builder->instance(AppEnv::class, $this->env);
        $builder->instance(Deployment::class, $this->deployment);
        $builder->instance(ClockInterface::class, new SystemClock());
        $builder->instance(EventDispatcher::class, new EventDispatcher());
        $builder->scoped(AppScope::class, fn() => new AppScope());

        $builder->singleton(LoggerBuilder::class);
        $builder->singleton(Logger::class, $this->buildLogger(...));

        $builder->singleton(ExceptionHandlerBuilder::class);
        $builder->singleton(ExceptionHandler::class, $this->buildExceptionHandler(...));

        foreach ($this->initializers as $initializer) {
            $initializer::register($builder, $this->env);
        }
    }

    /**
     * @param Container $container
     * @return void
     */
    protected function initializeServices(Container $container): void
    {
        foreach ($this->initializers as $initializer) {
            $container->make($initializer)->initialize();
        }
    }

    /**
     * @param class-string<ServiceInitializer> $initializer
     * @return $this
     */
    public function addInitializer(string $initializer): static
    {
        $this->initializers[] = $initializer;
        return $this;
    }

    /**
     * @param AppLifeCycle $service
     * @return $this
     */
    public function addLifeCycleService(AppLifeCycle $service): static
    {
        $this->lifeCycles[] = $service;
        return $this;
    }

    /**
     * @return AppEnv
     */
    protected function instantiateAppEnv(): AppEnv
    {
        return new AppEnv(
            Env::getString('NAMESPACE'),
            Env::getBoolOrNull('DEVELOP_MODE') ?? false,
            Env::getBoolOrNull('TEST_MODE') ?? false,
        );
    }

    /**
     * @return Deployment
     */
    protected function instantiateDeployment(): Deployment
    {
        return new Deployment(
            Env::getStringOrNull('DEPLOYER') ?? 'unknown',
            Env::getStringOrNull('REVISION') ?? 'unknown',
            Env::getFloatOrNull('DEPLOY_TIME') ?? 0.0,
        );
    }

    /**
     * @param Container $container
     * @return Logger
     */
    protected function buildLogger(Container $container): Logger
    {
        return $container->pull(LoggerBuilder::class)->build();
    }

    /**
     * @param Container $container
     * @return ExceptionHandler
     */
    protected function buildExceptionHandler(Container $container): ExceptionHandler
    {
        return $container->pull(ExceptionHandlerBuilder::class)->build();
    }

    /**
     * @return string
     */
    protected function generateRunId(): string
    {
        return new NanoIdGenerator()->generate(8);
    }
}
