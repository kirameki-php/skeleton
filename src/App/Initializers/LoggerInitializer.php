<?php declare(strict_types=1);

namespace Kirameki\App\Initializers;

use Kirameki\Framework\Foundation\ServiceInitializer;
use Kirameki\Framework\Logging\BuildsLogger;
use Kirameki\Framework\Logging\LogLevel;
use Kirameki\Framework\Logging\Writers\ConsoleWriter;

class LoggerInitializer extends ServiceInitializer
{
    use BuildsLogger;

    public function initialize(): void
    {
        $this->logger
            ->addWriter('console', new ConsoleWriter(LogLevel::Info));
    }
}
