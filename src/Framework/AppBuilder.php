<?php declare(strict_types=1);

namespace Kirameki\Framework;

use Kirameki\Container\Container;
use Kirameki\Container\ContainerBuilder;
use Kirameki\Container\EntryCollection;
use Kirameki\Container\FixedEntry;
use Kirameki\Framework\Console\CommandRegistry;
use Kirameki\Framework\Console\ConsoleInitializer;
use Kirameki\Framework\Crypto\NanoIdGenerator;
use Kirameki\Framework\Foundation\AppEnv;
use Kirameki\Framework\Foundation\AppLifeCycle;
use Kirameki\Framework\Foundation\Deployment;
use Kirameki\Framework\Foundation\FoundationInitializer;
use Kirameki\Framework\Foundation\ServiceInitializer;
use Kirameki\Framework\Http\HttpInitializer;
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
     * @param CommandRegistry $commands
     */
    public function __construct(
        public readonly Path $path,
        protected array $initializers = [],
        protected array $lifeCycles = [],
        protected readonly NanoIdGenerator $idGenerator = new NanoIdGenerator(),
        protected readonly CommandRegistry $commands = new CommandRegistry(),
    ) {
        $this->env = $this->instantiateAppEnv();
        $this->deployment = $this->instantiateDeployment();
        $this->runId = $this->generateRunId();
        $this->startTimeSeconds = microtime(true);

        $this->addInitializer(FoundationInitializer::class);
        $this->addInitializer(ConsoleInitializer::class);
        $this->addInitializer(HttpInitializer::class);
    }

    /**
     * @return App
     */
    public function build(): App
    {
        $entries = new EntryCollection();
        $container =  $this->prepareContainer($entries);
        $app = new App($this->path, $container, $this->runId, $this->lifeCycles, $this->startTimeSeconds);

        $entries->set(Container::class, new FixedEntry($container));
        $entries->set(App::class, new FixedEntry($app));
        $entries->set(AppEnv::class, new FixedEntry($this->env));
        $entries->set(Deployment::class, new FixedEntry($this->deployment));

        return $app;
    }

    /**
     * @param EntryCollection $entries
     * @return Container
     */
    protected function prepareContainer(EntryCollection $entries): Container
    {
        $builder = new ContainerBuilder($entries);

        foreach ($this->initializers as $initializer) {
            new $initializer($this, $builder, $this->commands)->initialize();
        }

        return $builder->build();
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
            $this->path,
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
