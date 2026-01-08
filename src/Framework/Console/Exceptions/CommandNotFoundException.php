<?php declare(strict_types=1);

namespace Kirameki\Framework\Console\Exceptions;

use Kirameki\Process\ExitCode;

class CommandNotFoundException extends CliException
{
    /**
     * @return int
     */
    public function getExitCode(): int
    {
        return ExitCode::COMMAND_NOT_FOUND;
    }
}
