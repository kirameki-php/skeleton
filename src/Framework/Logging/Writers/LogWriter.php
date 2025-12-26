<?php declare(strict_types=1);

namespace Kirameki\Framework\Logging\Writers;

use Kirameki\Framework\Logging\LogLevel;
use Kirameki\Framework\Logging\LogRecord;

abstract class LogWriter
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
     * @param LogLevel $level
     * @return $this
     */
    public function setLevel(LogLevel $level): static
    {
        $this->level = $level;
        return $this;
    }

    /**
     * @param LogRecord $record
     * @return void
     */
    public function write(LogRecord $record): void
    {
        if ($this->isEnabled($record->level)) {
            $this->handle($record);
        }
    }

    /**
     * @return void
     */
    abstract public function close(): void;

    /**
     * @param LogRecord $record
     * @return void
     */
    abstract protected function handle(LogRecord $record): void;
}
