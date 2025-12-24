<?php

namespace Kirameki\Framework\Logging;

enum LogLevel: int
{
    /**
     * Debug or trace information.
     */
    case Debug = 100;

    /**
     * Routine information, such as ongoing status or performance.
     */
    case Info = 200;

    /**
     * Normal but significant events, such as start up, shut down, or a configuration change.
     */
    case Notice = 300;

    /**
     * Warning events might cause problems.
     */
    case Warning = 400;

    /**
     * Error events are likely to cause problems.
     */
    case Error = 500;

    /**
     * Critical conditions
     *
     * Example: Application component unavailable, unexpected exception.
     */
    case Critical = 600;

    /**
     * A person must take an action immediately.
     */
    case Alert = 700;

    /**
     * One or more systems are unusable.
     */
    case Emergency = 800;

    /**
     * @return string
     */
    public function shortName(): string
    {
        return match ($this) {
            LogLevel::Debug => 'DBG',
            LogLevel::Info => 'INF',
            LogLevel::Notice => 'NOT',
            LogLevel::Warning => 'WRN',
            LogLevel::Error => 'ERR',
            LogLevel::Critical => 'CRT',
            LogLevel::Alert => 'ALT',
            LogLevel::Emergency => 'EMG',
        };
    }
}
