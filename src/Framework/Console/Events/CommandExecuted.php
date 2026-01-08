<?php declare(strict_types=1);

namespace Kirameki\Framework\Console\Events;

use Kirameki\Event\Event;
use Kirameki\Framework\Console\Command;

class CommandExecuted extends Event
{
    /**
     * @param Command $command
     * @param int $exitCode
     */
    public function __construct(
        public readonly Command $command,
        public readonly int $exitCode,
    )
    {
    }
}
