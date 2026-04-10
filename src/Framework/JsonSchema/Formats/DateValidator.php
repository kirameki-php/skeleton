<?php declare(strict_types=1);

namespace Kirameki\Framework\JsonSchema\Formats;

use Kirameki\Framework\JsonSchema\JsonSchema;
use Kirameki\Framework\JsonSchema\ValidationResultBuilder;
use Override;

class DateValidator extends FormatValidator
{
    const string FORMAT = 'date';

    /**
     * @inheritDoc
     */
    #[Override]
    public function validate(JsonSchema $schema, object $data, array $path, ValidationResultBuilder $result): void
    {
        $value = $this->getValueFromPath($data, $path);

        $pattern = '/^
            \d{4}                       # year
            -(?:0[1-9]|1[0-2])          # month (01-12)
            -(?:0[1-9]|[12]\d|3[01])    # day   (01-31)
        $/ix';

        if (!preg_match($pattern, $value)) {
            $result->addError($path, "Value must be a valid date format, got: {$value}", ['got' => $value]);
        }
    }
}
