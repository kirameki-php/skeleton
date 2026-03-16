<?php declare(strict_types=1);

namespace Kirameki\Framework\JsonSchema;

class JsonSchemaValidator
{
    public function __construct()
    {
    }

    public function validate(JsonSchema $jsonSchema, string $path = '$'): ValidationResult
    {
        $builder = new ValidationResultBuilder();
        $this->doValidate($jsonSchema, $path, $builder);
        return $builder->build();
    }

    private function doValidate(JsonSchema $jsonSchema, string $path, ValidationResultBuilder $builder): void
    {
        $this->validateBooleanType($jsonSchema, $path, $builder);

        foreach ($jsonSchema->properties ?? [] as $name => $propertySchema) {
            $this->doValidate($propertySchema, "{$path}.{$name}", $builder);
        }

        foreach ($jsonSchema->patternProperties ?? [] as $pattern => $propertySchema) {
            $this->doValidate($propertySchema, "{$path}[patternProperties/{$pattern}]", $builder);
        }

        foreach ($jsonSchema->allOf ?? [] as $index => $schema) {
            $this->doValidate($schema, "{$path}[allOf/{$index}]", $builder);
        }

        foreach ($jsonSchema->anyOf ?? [] as $index => $schema) {
            $this->doValidate($schema, "{$path}[anyOf/{$index}]", $builder);
        }

        foreach ($jsonSchema->oneOf ?? [] as $index => $schema) {
            $this->doValidate($schema, "{$path}[oneOf/{$index}]", $builder);
        }

        foreach ($jsonSchema->dependentSchemas ?? [] as $key => $schema) {
            $this->doValidate($schema, "{$path}[dependentSchemas/{$key}]", $builder);
        }

        $namedSchemas = [
            'contains'  => $jsonSchema->contains,
            'if'        => $jsonSchema->if,
            'then'      => $jsonSchema->then,
            'else'      => $jsonSchema->else,
            'items'     => $jsonSchema->items,
            'not'       => $jsonSchema->not,
        ];

        foreach ($namedSchemas as $keyword => $schema) {
            if ($schema !== null) {
                $this->doValidate($schema, "{$path}[{$keyword}]", $builder);
            }
        }
    }

    private function validateBooleanType(JsonSchema $jsonSchema, string $path, ValidationResultBuilder $builder): void
    {
        if ($jsonSchema->const !== null && !is_bool($jsonSchema->const)) {
            $builder->addError("{$path}.const", '`const` must be bool when validating boolean schema.');
        }

        if ($jsonSchema->default !== null && !is_bool($jsonSchema->default)) {
            $builder->addError("{$path}.default", '`default` must be bool when validating boolean schema.');
        }

        if ($jsonSchema->enum === null) {
            return;
        }

        foreach ($jsonSchema->enum as $index => $enumValue) {
            if (!is_bool($enumValue)) {
                $builder->addError("{$path}.enum[{$index}]", sprintf('`enum[%d]` must be bool when validating boolean schema.', $index));
            }
        }
    }
}
