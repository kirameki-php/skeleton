<?php declare(strict_types=1);

namespace Kirameki\Framework\JsonSchema;

class ValidationResultBuilder
{
    /**
     * @var list<ValidationError>
     */
    private array $errors = [];

    /**
     * @param string $path
     * @param string $message
     * @return $this
     */
    public function addError(string $path, string $message): static
    {
        $this->errors[] = new ValidationError($path, $message);
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

