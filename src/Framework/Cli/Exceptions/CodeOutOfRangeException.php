<?php declare(strict_types=1);

namespace Kirameki\Framework\Cli\Exceptions;

use Kirameki\Process\ExitCode;

class CodeOutOfRangeException extends CliException
{
    /**
     * @return int
     */
    public function getExitCode(): int
    {
        return ExitCode::STATUS_OUT_OF_RANGE;
    }
}
