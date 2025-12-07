<?php declare(strict_types=1);

namespace App\Framework\LifeCycle;

use function hrtime;

/**
 * Represents the scope of the application during its runtime.
 */
final class AppScope
{
    /**
     * @var float The start time in seconds since the Unix epoch.
     */
    protected readonly float $startTimeSeconds;

    /**
     * Initializes with the start time in seconds since the Unix epoch as a float.
     */
    public function __construct()
    {
        $this->startTimeSeconds = hrtime(true) / 1_000_000_000;
    }

    /**
     * Gets the elapsed time in seconds since the scope was created.
     *
     * @return float
     */
    public function getElapsedSeconds(): float
    {
        return (hrtime(true) / 1_000_000_000) - $this->startTimeSeconds;
    }
}
