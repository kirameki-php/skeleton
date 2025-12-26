<?php declare(strict_types=1);

namespace Kirameki\Framework\Logging\Writers;

use Kirameki\Framework\Logging\Formatters\ConsoleFormatter;
use Kirameki\Framework\Logging\LogLevel;

class ConsoleWriter extends StreamWriter
{
    /**
     * @param LogLevel $level
     */
    public function __construct(
        LogLevel $level,
    ) {
        parent::__construct('php://stdout', $level, new ConsoleFormatter());
    }
}
