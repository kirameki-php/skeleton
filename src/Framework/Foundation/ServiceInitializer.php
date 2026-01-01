<?php declare(strict_types=1);

namespace Kirameki\Framework\Foundation;

use Kirameki\Container\Container;
use Kirameki\Framework\App;

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
}
