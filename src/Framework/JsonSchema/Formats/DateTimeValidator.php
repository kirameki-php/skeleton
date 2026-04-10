<?php declare(strict_types=1);

namespace Kirameki\Framework\JsonSchema\Formats;

use Kirameki\Framework\JsonSchema\JsonSchema;
use Kirameki\Framework\JsonSchema\ValidationResultBuilder;
use Override;

class DateTimeValidator extends FormatValidator
{
    const string FORMAT = 'date-time';

    /**
     * @inheritDoc
     */
    #[Override]
    public function validate(JsonSchema $schema, object $data, array $path, ValidationResultBuilder $result): void
    {
        $value = $this->getValueFromPath($data, $path);

        // Validate RFC 3339 date-time format (required by JSON Schema "date-time" format)
        $pattern = '/^
            \d{4}                       # year
            -(?:0[1-9]|1[0-2])          # month (01-12)
            -(?:0[1-9]|[12]\d|3[01])    # day   (01-31)
            T
            (?:[01]\d|2[0-3])           # hour   (00-23)
            :[0-5]\d                    # minute (00-59)
            :[0-5]\d                    # second (00-59)
            (?:\.\d+)?                  # optional fractional seconds
            (?:Z|[+-](?:[01]\d|2[0-3]):[0-5]\d)  # UTC or numeric offset
        $/ix';

        if (!preg_match($pattern, $value)) {
            $result->addError($path, "Value must be a valid date-time (RFC 3339), got: {$value}", ['got' => $value]);
        }
    }
}
