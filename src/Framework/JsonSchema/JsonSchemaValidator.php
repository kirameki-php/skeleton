<?php declare(strict_types=1);

namespace Kirameki\Framework\JsonSchema;

class JsonSchemaValidator
{
    public function __construct()
    {
    }

    public function validate(JsonSchema $schema, object $data): ValidationResult
    {
        $builder = new ValidationResultBuilder();
        $this->validateType($schema, $data, [], $builder);
        return $builder->build();
    }

    /**
     * @param JsonSchema $schema
     * @param mixed $data
     * @param list<string> $path
     * @param ValidationResultBuilder $result
     * @return void
     */
    protected function validateType(JsonSchema $schema, mixed $data, array $path, ValidationResultBuilder $result): void
    {
        if ($schema->type === null) {
            return;
        }

        $types = is_array($schema->type) ? $schema->type : [$schema->type];

        foreach ($types as $type) {
            match ($type) {
                DataType::Array => $this->validateArrayType($schema, $data, $path, $result),
                DataType::Boolean => $this->validateBooleanType($schema, $data, $path, $result),
                DataType::Integer => $this->validateIntegerType($schema, $data, $path, $result),
                DataType::Number => $this->validateNumberType($schema, $data, $path, $result),
                DataType::Object => $this->validateObjectType($schema, $data, $path, $result),
                DataType::String => $this->validateStringType($schema, $data, $path, $result),
                DataType::Null => $this->validateNullType($schema, $data, $path, $result),
            };
        }
    }

    /**
     * @param JsonSchema $schema
     * @param object $data
     * @param list<string> $path
     * @param ValidationResultBuilder $result
     * @return void
     */
    protected function validateBooleanType(JsonSchema $schema, object $data, array $path, ValidationResultBuilder $result): void
    {
        $value = $this->getValueFromPath($data, $path);
        if (!is_bool($value)) {
            $result->addError($path, 'Expected type: boolean, got: ' . get_debug_type($value), ['got' => $value]);
        }
    }

    /**
     * @param JsonSchema $schema
     * @param object $data
     * @param list<string> $path
     * @param ValidationResultBuilder $result
     * @return void
     */
    protected function validateIntegerType(JsonSchema $schema, object $data, array $path, ValidationResultBuilder $result): void
    {
        $value = $this->getValueFromPath($data, $path);

        if (!is_int($value)) {
            $result->addError($path, 'Expected type: integer, got ' . get_debug_type($value), ['got' => $value]);
            return;
        }

        $this->checkNumberBoundaries($schema, $value, $path, $result);
        $this->checkNumericMultiples($schema, $value, $path, $result);
        $this->checkEnum($schema->enum, $value, $path, $result);
    }

    /**
     * @param JsonSchema $schema
     * @param object $data
     * @param list<string> $path
     * @param ValidationResultBuilder $result
     * @return void
     */
    protected function validateNumberType(JsonSchema $schema, object $data, array $path, ValidationResultBuilder $result): void
    {
        $value = $this->getValueFromPath($data, $path);

        if (!is_int($value) && !is_float($value)) {
            $result->addError($path, 'Expected type: integer, got ' . get_debug_type($value));
        }

        $this->checkNumberBoundaries($schema, $value, $path, $result);
        $this->checkNumericMultiples($schema, $value, $path, $result);
        $this->checkEnum($schema->enum, $value, $path, $result);
    }

    /**
     * @param JsonSchema $schema
     * @param object $data
     * @param list<string> $path
     * @param ValidationResultBuilder $result
     * @return void
     */
    protected function validateStringType(JsonSchema $schema, object $data, array $path, ValidationResultBuilder $result): void
    {
        $value = $this->getValueFromPath($data, $path);
        if (!is_string($value)) {
            $result->addError($path, 'Expected type: string, got ' . get_debug_type($value));
            return;
        }

        $minLength = $schema->minLength;
        if ($minLength !== null && strlen($value) < $minLength) {
            $result->addError($path, "String length must be >= {$minLength}", ['got' => $value]);
        }

        $maxLength = $schema->maxLength;
        if ($maxLength !== null && strlen($value) > $maxLength) {
            $result->addError($path, "String length must be <= {$maxLength}", ['got' => $value]);
        }

        $pattern = $schema->pattern;
        if ($pattern !== null && !preg_match('~' . str_replace('~', '\~', $pattern) . '~', $value)) {
            $result->addError($path, "String must match pattern: {$pattern}", ['got' => $value]);
        }

        $this->checkEnum($schema->enum, $value, $path, $result);
    }

    /**
     * @param JsonSchema $schema
     * @param object $data
     * @param list<string> $path
     * @param ValidationResultBuilder $result
     * @return void
     */
    protected function validateArrayType(JsonSchema $schema, object $data, array $path, ValidationResultBuilder $result): void
    {
        $value = $this->getValueFromPath($data, $path);
        $size = count($value);

        if (!is_array($value)) {
            $result->addError($path, 'Expected type: array, got ' . get_debug_type($value));
        }

        $minItems = $schema->minItems;
        if ($minItems !== null && $size < $minItems) {
            $result->addError($path, "Array size must be >= {$minItems}", ['got' => $value]);
        }

        $maxItems = $schema->maxItems;
        if ($maxItems !== null && $size > $maxItems) {
            $result->addError($path, "Array size must be <= {$maxItems}", ['got' => $value]);
        }

        $items = $schema->items;
        if ($items !== null && $items !== false) {
            foreach ($value as $i => $item) {
                $this->validateType($items, $item, [...$path, (string)$i], $result);
            }
        }

        $prefixItems = $schema->prefixItems;
        if ($prefixItems !== null) {
            foreach ($value as $i => $item) {
                if (!isset($prefixItems[$i]) && $items === false) {
                    $result->addError([...$path, (string)$i], 'Additional items are not allowed', ['got' => $item]);
                } else {
                    $this->validateType($prefixItems[$i], $item, [...$path, (string)$i], $result);
                }
            }
        }
    }

    /**
     * @param JsonSchema $schema
     * @param object $data
     * @param list<string> $path
     * @param ValidationResultBuilder $result
     * @return void
     */
    protected function validateObjectType(JsonSchema $schema, object $data, array $path, ValidationResultBuilder $result): void
    {
    }

    /**
     * @param JsonSchema $schema
     * @param object $data
     * @param list<string> $path
     * @param ValidationResultBuilder $result
     * @return void
     */
    protected function validateNullType(JsonSchema $schema, object $data, array $path, ValidationResultBuilder $result): void
    {
        $value = $this->getValueFromPath($data, $path);
        if (!is_null($value)) {
            $result->addError($path, 'Expected type: null, got ' . get_debug_type($value));
        }
    }

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

    /**
     * @param JsonSchema $schema
     * @param int|float $value
     * @param list<string> $path
     * @param ValidationResultBuilder $result
     * @return void
     */
    protected function checkNumberBoundaries(JsonSchema $schema, mixed $value, array $path, ValidationResultBuilder $result): void
    {
        if ($schema->minimum !== null && $value < $schema->minimum) {
            $result->addError($path, "{$value} must be >= {$schema->minimum}", ['got' => $value]);
        }

        if ($schema->exclusiveMinimum !== null && $value <= $schema->exclusiveMinimum) {
            $result->addError($path, "{$value} must be > {$schema->exclusiveMinimum}", ['got' => $value]);
        }

        if ($schema->maximum !== null && $value > $schema->maximum) {
            $result->addError($path, "{$value} must be <= {$schema->maximum}", ['got' => $value]);
        }

        if ($schema->exclusiveMaximum !== null && $value > $schema->exclusiveMaximum) {
            $result->addError($path, "{$value} must be < {$schema->exclusiveMaximum}", ['got' => $value]);
        }
    }

    /**
     * @param JsonSchema $schema
     * @param mixed $value
     * @param list<string> $path
     * @param ValidationResultBuilder $result
     * @return void
     */
    protected function checkNumericMultiples(JsonSchema $schema, mixed $value, array $path, ValidationResultBuilder $result): void
    {
        if ($schema->multipleOf !== null) {
            $mod = $value % $schema->multipleOf;
            if ($mod !== 0) {
                $result->addError($path, "{$value} must be {$mod}", ['got' => $value]);
            }
        }
    }

    /**
     * @param list<string>|null $candidates
     * @param mixed $value
     * @param list<string> $path
     * @param ValidationResultBuilder $result
     * @return void
     */
    protected function checkEnum(?array $candidates, mixed $value, array $path, ValidationResultBuilder $result): void
    {
        if ($candidates !== null && !in_array($value, $candidates, true)) {
            $result->addError($path, "{$value} must be one of: " . implode(', ', $candidates), ['got' => $value]);
        }
    }
}
