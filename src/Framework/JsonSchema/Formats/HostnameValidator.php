<?php declare(strict_types=1);

namespace Kirameki\Framework\JsonSchema\Formats;

use Kirameki\Framework\JsonSchema\JsonSchema;
use Kirameki\Framework\JsonSchema\ValidationResultBuilder;
use Override;

class HostnameValidator extends FormatValidator
{
    const string FORMAT = 'hostname';

    /**
     * @inheritDoc
     */
    #[Override]
    public function validate(JsonSchema $schema, object $data, array $path, ValidationResultBuilder $result): void
    {
        $value = $this->getValueFromPath($data, $path);

        // Validate hostname format (RFC 1123)
        // Total length must not exceed 253 characters
        // Each label must be 1–63 characters, alphanumeric or hyphens,
        // and must not start or end with a hyphen.
        $pattern = '/^
            [a-zA-Z0-9]                             # label start: alphanumeric
            (?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?      # label body: up to 61 chars + end char
            (?:\.
                [a-zA-Z0-9]                         # next label start
                (?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?  # next label body
            )*
        $/x';

        if (!is_string($value) || strlen($value) > 253 || !preg_match($pattern, $value)) {
            $result->addError($path, "Value must be a valid hostname, got: {$value}", ['got' => $value]);
        }
    }
}
