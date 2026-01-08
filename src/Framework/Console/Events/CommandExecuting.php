<?php declare(strict_types=1);

namespace Kirameki\Framework\Console\Events;

use Kirameki\Event\Event;
use Kirameki\Framework\Console\Command;

class CommandExecuting extends Event
{
    /**
     * @param Command $command
     */
    public function __construct(
        public readonly Command $command,
    )
    {
    }
}
