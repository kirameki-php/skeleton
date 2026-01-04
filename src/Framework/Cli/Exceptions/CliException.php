<?php declare(strict_types=1);

namespace Kirameki\Framework\Cli\Exceptions;

use Kirameki\Exceptions\LogicException;

abstract class CliException extends LogicException
{
    /**
     * @return int
     */
    abstract public function getExitCode(): int;
}
