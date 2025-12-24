<?php declare(strict_types=1);

namespace Kirameki\Framework\Logging\Handlers;

use Kirameki\Framework\Logging\Formatters\Formatter;
use Kirameki\Framework\Logging\LogLevel;
use Kirameki\Framework\Logging\LogRecord;

abstract class Handler
{
    /**
     * @param LogLevel $level
     */
    public function __construct(
        protected LogLevel $level,
    ) {
    }

    /**
     * @param LogLevel $level
     * @return bool
     */
    public function isEnabled(LogLevel $level): bool
    {
        return $level->value >= $this->level->value;
    }

    /**
     * @param LogRecord $record
     * @return void
     */
    public function handle(LogRecord $record): void
    {
        if (!$this->isEnabled($record->level)) {
            return;
        }

        $this->modify($record);
        $this->write($record);
    }

    protected function modify(LogRecord $record): void
    {
        // Override to modify the record before writing
    }

    /**
     * @return void
     */
    abstract public function close(): void;

    /**
     * @param LogRecord $record
     * @return void
     */
    abstract protected function write(LogRecord $record): void;
}
