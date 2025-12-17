<?php declare(strict_types=1);

namespace Kirameki\Framework\Foundation\Initializers;

use Kirameki\Framework\Foundation\ServiceInitializer;
use Kirameki\Container\Container;
use Kirameki\Exception\ExceptionHandler;
use Kirameki\Exception\Reporters\LogReporter;
use Psr\Log\LoggerInterface;

class ExceptionInitializer implements ServiceInitializer
{
    function register(Container $container): void
    {
        $container->singleton(ExceptionHandler::class, function () use ($container) {
            $logger = $container->get(LoggerInterface::class);
            $reporter = new LogReporter($logger);
            return new ExceptionHandler($reporter, $reporter);
        });
    }
}
