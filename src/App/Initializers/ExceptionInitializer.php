<?php declare(strict_types=1);

namespace Kirameki\App\Initializers;

use Kirameki\Framework\Exception\ExceptionHandlerBuilder;
use Kirameki\Framework\Exception\Reporters\LogReporter;
use Kirameki\Framework\Foundation\ServiceInitializer;

class ExceptionInitializer extends ServiceInitializer
{
    public function initialize(): void
    {
        $this->container->get(ExceptionHandlerBuilder::class)
            ->addReporter(LogReporter::class)
            ->addDeprecatedReporter(LogReporter::class)
            ->setFallbackReporter(LogReporter::class);
    }
}
