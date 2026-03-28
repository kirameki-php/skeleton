<?php declare(strict_types=1);

namespace Kirameki\Framework\JsonSchema;

class ValidationResultBuilder
{
    /**
     * @var list<ValidationError>
     */
    private array $errors = [];

    /**
     * @param list<string> $path
     * @param string $message
     * @param array<string, mixed> $context
     * @return $this
     */
    public function addError(array $path, string $message, array $context = []): static
    {
        $this->errors[] = new ValidationError(implode('.', $path), $message, $context);
        return $this;
    }

    /**
     * @param ValidationError $error
     * @return $this
     */
    public function push(ValidationError $error): static
    {
        $this->errors[] = $error;
        return $this;
    }

    /**
     * @return ValidationResult
     */
    public function build(): ValidationResult
    {
        return new ValidationResult($this->errors);
    }
}

