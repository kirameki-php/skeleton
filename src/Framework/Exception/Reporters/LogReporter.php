<?php declare(strict_types=1);

namespace Kirameki\Framework\Exception\Reporters;

use Kirameki\Framework\Logging\Logger;
use Throwable;

class LogReporter implements Reporter
{
    /**
     * @param Logger $logger
     */
    public function __construct(
        protected Logger $logger,
    ) {
    }

    /**
     * @param Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        $message = $exception->getMessage();
        $context = ['exception' => $exception];
        $this->logger->error($message, $context);
    }
}
