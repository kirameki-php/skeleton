<?php declare(strict_types=1);

namespace Kirameki\Framework\JsonSchema;

class ValidationError
{
    public function __construct(
        public readonly string $path,
        public readonly string $message,
    ) {
    }
}

