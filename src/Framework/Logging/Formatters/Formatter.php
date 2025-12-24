<?php declare(strict_types=1);

namespace Kirameki\Framework\Logging\Formatters;

use Kirameki\Framework\Logging\LogRecord;

interface Formatter
{
    function format(LogRecord $record): string;
}
