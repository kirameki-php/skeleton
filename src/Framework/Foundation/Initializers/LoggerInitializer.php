<?php declare(strict_types=1);

namespace Kirameki\Framework\Foundation\Initializers;

use Kirameki\Container\Container;
use Kirameki\Framework\Foundation\ServiceInitializer;
use Kirameki\Framework\Logging\Formatters\ConsoleFormatter;
use Kirameki\Framework\Logging\Handlers\StreamHandler;
use Kirameki\Framework\Logging\Logger;
use Kirameki\Framework\Logging\LogLevel;

class LoggerInitializer implements ServiceInitializer
{
    function register(Container $container): void
    {
        $container->instance(Logger::class, new Logger([
            new StreamHandler('php://stdout', LogLevel::Debug, new ConsoleFormatter()),
        ]));
    }
}
