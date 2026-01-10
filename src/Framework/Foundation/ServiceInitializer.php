<?php declare(strict_types=1);

namespace Kirameki\Framework\Foundation;

use Kirameki\Container\Container;
use Kirameki\Container\ContainerBuilder;
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
     * @param ContainerBuilder $container
     * @param AppEnv $env
     * @return void
     */
    public static function register(ContainerBuilder $container, AppEnv $env): void
    {
        // Override in subclass
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
    public function initialize(): void
    {
        // Override in subclass
    }
}
