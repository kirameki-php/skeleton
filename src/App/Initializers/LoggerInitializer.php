<?php declare(strict_types=1);

namespace Kirameki\App\Initializers;

use Kirameki\Framework\Logging\LoggerBuilder;
use Kirameki\Framework\Logging\LoggerInitializerBase;
use Kirameki\Framework\Logging\LogLevel;
use Kirameki\Framework\Logging\Writers\ConsoleWriter;

class LoggerInitializer extends LoggerInitializerBase
{
    protected function build(LoggerBuilder $builder): void
    {
        $builder
            ->addWriter('console', new ConsoleWriter(LogLevel::Info));
    }
}
