<?php declare(strict_types=1);

namespace Kirameki\App\Initializers;

use Kirameki\Framework\Exception\BuildsExceptionHandler;
use Kirameki\Framework\Exception\Reporters\LogReporter;
use Kirameki\Framework\Foundation\ServiceInitializer;

class ExceptionInitializer extends ServiceInitializer
{
    use BuildsExceptionHandler;

    public function initialize(): void
    {
        $this->exceptionHandler
            ->addReporter(LogReporter::class)
            ->addDeprecatedReporter(LogReporter::class)
            ->setFallbackReporter(LogReporter::class);
    }
}
