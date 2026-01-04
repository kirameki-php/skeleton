<?php declare(strict_types=1);

namespace Kirameki\Framework\Foundation;

use Kirameki\Collections\Utils\Arr;
use Kirameki\Container\Container;
use Kirameki\Framework\App;
use Kirameki\Framework\Cli\Command;
use Kirameki\Framework\Cli\CommandRegistry;

abstract class ServiceInitializer
{
    /**
     * @param App $app
     */
    public function __construct(
        protected App $app,
    ) {
    }

    /**
     * @param Container $container
     * @return void
     */
    abstract public function register(Container $container): void;

    /**
     * @param iterable<array-key, class-string<Command>> $command
     * @return void
     */
    public function addCommands(iterable $commands): void
    {
        $registry = $this->app->container->get(CommandRegistry::class);
        Arr::each($commands, $registry->register(...));
    }
}
