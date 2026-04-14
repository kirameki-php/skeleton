<?php declare(strict_types=1);

namespace Kirameki\Framework\JsonSchema\Formats;

use Kirameki\Framework\JsonSchema\JsonSchema;
use Kirameki\Framework\JsonSchema\ValidationResultBuilder;
use Override;

class Int32Validator extends FormatValidator
{
    const string FORMAT = 'int32';

    /**
     * @inheritDoc
     */
    #[Override]
    public function validate(JsonSchema $schema, object $data, array $path, ValidationResultBuilder $result): void
    {
        $value = $this->getValueFromPath($data, $path);

        if (!is_numeric($value)) {
            $result->addError($path, "Value must be a number, got: {$value}", ['got' => $value]);
            return;
        }

        $i = (int)$value;

        if ($i < -2_147_483_648 || $i > 2_147_483_647) {
            $result->addError($path, "Value must be a 32-bit integer, got: {$value}", ['got' => $value]);
        }
    }
}
