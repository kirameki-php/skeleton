<?php declare(strict_types=1);

namespace Kirameki\Framework\JsonSchema\Formats;

use Kirameki\Framework\JsonSchema\JsonSchema;
use Kirameki\Framework\JsonSchema\ValidationResultBuilder;
use Override;

class Int64Validator extends FormatValidator
{
    const string FORMAT = 'int64';

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

        $str = (string)$value;

        if (bccomp($str, '-9223372036854775808') < 0 || bccomp($str, '9223372036854775807') > 0) {
            $result->addError($path, "Value must be a 64-bit integer, got: {$value}", ['got' => $value]);
        }
    }
}
