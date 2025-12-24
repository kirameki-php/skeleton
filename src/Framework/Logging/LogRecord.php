<?php declare(strict_types=1);

namespace Kirameki\Framework\Logging;

final class LogRecord
{
    /**
     * @param LogLevel $level
     * @param string $message
     * @param array<string, mixed> $context
     * @param float $time
     */
    public function __construct(
        public LogLevel $level,
        public string $message,
        public array $context,
        public float $time,
    ) {
    }
}
