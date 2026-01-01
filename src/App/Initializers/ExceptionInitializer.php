<?php declare(strict_types=1);

namespace Kirameki\App\Initializers;

use Kirameki\Framework\Exception\ExceptionHandlerBuilder;
use Kirameki\Framework\Exception\ExceptionInitializerBase;
use Kirameki\Framework\Exception\Reporters\LogReporter;

class ExceptionInitializer extends ExceptionInitializerBase
{
    protected function setup(ExceptionHandlerBuilder $handler): void
    {
        $handler
            ->addReporter(LogReporter::class)
            ->addDeprecatedReporter(LogReporter::class)
            ->setFallbackReporter(LogReporter::class);
    }
}
