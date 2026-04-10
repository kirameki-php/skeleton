<?php declare(strict_types=1);

namespace Kirameki\Framework\JsonSchema\Formats;

use Kirameki\Framework\JsonSchema\JsonSchema;
use Kirameki\Framework\JsonSchema\ValidationResultBuilder;
use Override;

class TimeValidator extends FormatValidator
{
    const string FORMAT = 'time';

    /**
     * @inheritDoc
     */
    #[Override]
    public function validate(JsonSchema $schema, object $data, array $path, ValidationResultBuilder $result): void
    {
        $value = $this->getValueFromPath($data, $path);

        $pattern = '/^
            (?:[01]\d|2[0-3])           # hour   (00-23)
            :[0-5]\d                    # minute (00-59)
            :[0-5]\d                    # second (00-59)
            (?:\.\d+)?                  # optional fractional seconds
            (?:Z|[+-](?:[01]\d|2[0-3]):[0-5]\d)  # UTC or numeric offset
        $/ix';

        if (!preg_match($pattern, $value)) {
            $result->addError($path, "Value must be a valid time format, got: {$value}", ['got' => $value]);
        }
    }
}
