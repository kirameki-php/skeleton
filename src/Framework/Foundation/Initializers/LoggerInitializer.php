<?php declare(strict_types=1);

namespace Kirameki\Framework\Foundation\Initializers;

use Kirameki\Framework\Foundation\ServiceInitializer;
use Kirameki\Container\Container;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class LoggerInitializer implements ServiceInitializer
{
    function register(Container $container): void
    {
        $logger = new Logger('app');

        $container->instance(LoggerInterface::class, $logger);
    }
}
