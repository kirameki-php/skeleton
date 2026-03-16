<?php declare(strict_types=1);

namespace Kirameki\Framework\JsonSchema;

class ValidationResult
{
    /**
     * @param list<ValidationError> $errors
     */
    public function __construct(
        public readonly array $errors = [],
    ) {
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->errors === [];
    }
}
