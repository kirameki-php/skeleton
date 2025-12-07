<?php declare(strict_types=1);

namespace App\Framework\Logging;

use App\Framework\Initializer;
use Kirameki\Container\Container;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class LogInitializer implements Initializer
{
    function register(Container $container): void
    {
        $container->instance(LoggerInterface::class, new Logger('app'));
    }
}
