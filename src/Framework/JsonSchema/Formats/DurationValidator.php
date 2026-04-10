<?php declare(strict_types=1);

namespace Kirameki\Framework\JsonSchema\Formats;

use DateInterval;
use Exception;
use Kirameki\Framework\JsonSchema\JsonSchema;
use Kirameki\Framework\JsonSchema\ValidationResultBuilder;
use Override;

class DurationValidator extends FormatValidator
{
    const string FORMAT = 'duration';

    /**
     * @inheritDoc
     */
    #[Override]
    public function validate(JsonSchema $schema, object $data, array $path, ValidationResultBuilder $result): void
    {
        $value = $this->getValueFromPath($data, $path);

        try {
            new DateInterval($value);
        } catch (Exception) {
            $result->addError($path, "Value must be a valid ISO 8601 duration, got: {$value}", ['got' => $value]);
        }
    }
}
