<?php declare(strict_types=1);

namespace Kirameki\Framework\Foundation;

use Kirameki\Container\Container;
use Kirameki\Framework\App;
use Kirameki\Framework\Console\CommandRegistry;
use Kirameki\Framework\Http\Routing\HttpRouterBuilder;

abstract class ServiceInitializer
{
    /**
     * @var Container
     */
    protected Container $container;

    /**
     * @var HttpRouterBuilder
     */
    protected HttpRouterBuilder $router {
        get => $this->router ??= $this->app->container->get(HttpRouterBuilder::class);
    }

    /**
     * @var CommandRegistry
     */
    protected CommandRegistry $commands {
        get => $this->commands ??= $this->app->container->get(CommandRegistry::class);
    }

    /**
     * @param Container $container
     * @param AppEnv $env
     * @return void
     */
    public static function register(Container $container, AppEnv $env): void
    {
    }

    /**
     * @param App $app
     */
    public function __construct(
        protected App $app,
    ) {
        $this->container = $app->container;
    }

    /**
     * @return void
     */
    abstract public function initialize(): void;
}
