<?php declare(strict_types=1);

namespace Kirameki\App\Initializers;

use Kirameki\Framework\Foundation\ServiceInitializer;
use Kirameki\Framework\Logging\LoggerBuilder;
use Kirameki\Framework\Logging\LogLevel;
use Kirameki\Framework\Logging\Writers\ConsoleWriter;

class LoggerInitializer extends ServiceInitializer
{
    public function initialize(): void
    {
        $this->container->get(LoggerBuilder::class)
            ->addWriter('console', new ConsoleWriter(LogLevel::Info));
    }
}
