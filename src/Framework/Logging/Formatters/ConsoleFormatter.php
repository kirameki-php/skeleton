<?php declare(strict_types=1);

namespace Kirameki\Framework\Logging\Formatters;

use DateTime;
use Kirameki\Framework\Logging\LogRecord;
use function sprintf;

class ConsoleFormatter implements Formatter
{
    /**
     * @param LogRecord $record
     * @return string
     */
    function format(LogRecord $record): string
    {
        return sprintf(
            "[%s][%s] %s\n",
            DateTime::createFromTimestamp($record->time)->format('Y-m-d H:i:s.v'),
            $record->level->shortName(),
            $record->message,
        );
    }
}
