<?php declare(strict_types=1);

namespace Kirameki\Framework\JsonSchema\Formats;

use Kirameki\Framework\JsonSchema\JsonSchema;
use Kirameki\Framework\JsonSchema\ValidationResultBuilder;

abstract class FormatValidator
{
    /**
     * @param JsonSchema $schema
     * @param object $data
     * @param list<string> $path
     * @param ValidationResultBuilder $result
     * @return void
     */
    abstract public function validate(JsonSchema $schema, object $data, array $path, ValidationResultBuilder $result): void;

    /**
     * @param object $data
     * @param list<string> $path
     * @return mixed
     */
    protected function getValueFromPath(object $data, array $path): mixed
    {
        $current = $data;
        foreach ($path as $part) {
            $current = $data->$part ?? null;
        }
        return $current;
    }
}
