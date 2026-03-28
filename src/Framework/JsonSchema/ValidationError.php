<?php declare(strict_types=1);

namespace Kirameki\Framework\JsonSchema;

class ValidationError
{
    /**
     * @param string $path
     * @param string $message
     * @param array<string, mixed> $context
     */
    public function __construct(
        public readonly string $path,
        public readonly string $message,
        public readonly array $context,
    ) {
    }
}
