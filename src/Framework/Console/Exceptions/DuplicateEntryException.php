<?php declare(strict_types=1);

namespace Kirameki\Framework\Console\Exceptions;

use Kirameki\Exceptions\LogicException;
use Throwable;

class DuplicateEntryException extends LogicException
{
    public function __construct(string $name, ?iterable $context = null, int $code = 0, ?Throwable $previous = null)
    {
        $message = "Command: {$name} is already registered.";
        parent::__construct($message, $context, $code, $previous);
    }
}
