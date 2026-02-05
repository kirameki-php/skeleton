<?php declare(strict_types=1);

namespace Kirameki\Framework\Foundation;

use Kirameki\Clock\ClockInterface;
use Kirameki\Clock\SystemClock;
use Kirameki\Event\EventDispatcher;
use Kirameki\Framework\Exception\ExceptionHandler;
use Kirameki\Framework\Exception\ExceptionHandlerBuilder;
use Kirameki\Framework\Logging\Logger;
use Kirameki\Framework\Logging\LoggerBuilder;
use Override;

class FoundationInitializer extends ServiceInitializer
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function initialize(): void
    {
        $container = $this->container;

        $container->instance(ClockInterface::class, new SystemClock());
        $container->instance(EventDispatcher::class, new EventDispatcher());
        $container->scoped(AppScope::class, fn() => new AppScope());
        $container->builder(LoggerBuilder::class, Logger::class);
        $container->builder(ExceptionHandlerBuilder::class, ExceptionHandler::class);
    }
}
