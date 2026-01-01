<?php declare(strict_types=1);

namespace Kirameki\App\Initializers;

use Kirameki\Framework\Logging\LoggerBuilder;
use Kirameki\Framework\Logging\LoggerInitializerAbstract;
use Kirameki\Framework\Logging\LogLevel;
use Kirameki\Framework\Logging\Writers\ConsoleWriter;

class LoggerInitializer extends LoggerInitializerAbstract
{
    protected function setup(LoggerBuilder $builder): void
    {
        $builder
            ->addWriter('console', new ConsoleWriter(LogLevel::Debug));
    }
}
