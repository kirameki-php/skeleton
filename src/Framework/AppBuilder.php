<?php declare(strict_types=1);

namespace Kirameki\Framework;

use Kirameki\Container\Container;
use Kirameki\Framework\Console\ConsoleInitializer;
use Kirameki\Framework\Crypto\NanoIdGenerator;
use Kirameki\Framework\Foundation\AppEnv;
use Kirameki\Framework\Foundation\AppLifeCycle;
use Kirameki\Framework\Foundation\Deployment;
use Kirameki\Framework\Foundation\ServiceInitializer;
use Kirameki\Framework\Http\WebInitializer;
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
     * @param list<class-string<ServiceInitializer>> $initializers
     * @param list<AppLifeCycle> $lifeCycles
     * @param NanoIdGenerator $idGenerator
     */
    public function __construct(
        public readonly Path $path,
        protected Container $container = new Container(),
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
            $this->container,
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
            $this->container,
            $this->env,
            $this->deployment,
            $this->runId,
            $this->initializers,
            $this->lifeCycles,
            $this->startTimeSeconds,
        );
    }

    /**
     * @param class-string<ServiceInitializer> $initializer
     * @return void
     */
    public function addInitializer(string $initializer): void
    {
        $this->initializers[] = $initializer;
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
     * @return string
     */
    protected function generateRunId(): string
    {
        return new NanoIdGenerator()->generate(8);
    }
}
