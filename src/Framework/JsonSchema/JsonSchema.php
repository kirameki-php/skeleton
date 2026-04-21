<?php declare(strict_types=1);

namespace Kirameki\Framework\JsonSchema;

use Kirameki\Collections\Utils\Arr;

final class JsonSchema
{
    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function from(array $data): self
    {
        return new self(
            _id: $data['$id'] ?? null,
            _comment: $data['$comment'] ?? null,
            _defs: self::mapOfSchemas($data['$defs'] ?? null),
            _dynamicAnchor: $data['$dynamicAnchor'] ?? null,
            _dynamicRef: $data['$dynamicRef'] ?? null,
            _ref: $data['$ref'] ?? null,
            _schema: $data['$schema'] ?? 'https://json-schema.org/draft/2020-12/schema',
            _vocabulary: $data['$vocabulary'] ?? 'https://json-schema.org/draft/2020-12/schema',
            additionalProperties: $data['additionalProperties'] ?? false,
            allOf: self::listOfSchemas($data['allOf'] ?? null),
            anyOf: self::listOfSchemas($data['anyOf'] ?? null),
            const: $data['const'] ?? null,
            contains: self::maybeSchema($data['contains'] ?? null),
            contentEncoding: $data['contentEncoding'] ?? null,
            contentMediaType: $data['contentMediaType'] ?? null,
            contentSchema: $data['contentSchema'] ?? null,
            default: $data['default'] ?? null,
            dependentRequired: $data['dependentRequired'] ?? null,
            dependentSchemas: self::mapOfSchemas($data['dependentSchemas'] ?? null),
            deprecated: $data['deprecated'] ?? false,
            description: $data['description'] ?? null,
            else: self::maybeSchema($data['else'] ?? null),
            enum: $data['enum'] ?? null,
            examples: $data['examples'] ?? null,
            exclusiveMaximum: $data['exclusiveMaximum'] ?? null,
            exclusiveMinimum: $data['exclusiveMinimum'] ?? null,
            format: $data['format'] ?? null,
            if: self::maybeSchema($data['if'] ?? null),
            items: isset($data['items']) && $data['items'] === false
                ? false
                : self::maybeSchema($data['items'] ?? null),
            maximum: $data['maximum'] ?? null,
            maxItems: $data['maxItems'] ?? null,
            maxLength: $data['maxLength'] ?? null,
            maxProperties: $data['maxProperties'] ?? null,
            minimum: $data['minimum'] ?? null,
            minItems: $data['minItems'] ?? null,
            minLength: $data['minLength'] ?? null,
            minProperties: $data['minProperties'] ?? null,
            multipleOf: $data['multipleOf'] ?? null,
            not: self::maybeSchema($data['not'] ?? null),
            oneOf: self::listOfSchemas($data['oneOf'] ?? null),
            pattern: $data['pattern'] ?? null,
            patternProperties: self::mapOfSchemas($data['patternProperties'] ?? null),
            prefixItems: self::listOfSchemas($data['prefixItems'] ?? null),
            properties: self::mapOfSchemas($data['properties'] ?? null),
            propertyNames: self::mapOfSchemas($data['propertyNames'] ?? null),
            readOnly: $data['readOnly'] ?? false,
            required: $data['required'] ?? null,
            then: self::maybeSchema($data['then'] ?? null),
            title: $data['title'] ?? null,
            type: self::parseType($data['type'] ?? null),
            unevaluatedItems: $data['unevaluatedItems'] ?? null,
            unevaluatedProperties: $data['unevaluatedProperties'] ?? null,
            uniqueItems: $data['uniqueItems'] ?? null,
            writeOnly: $data['writeOnly'] ?? false,
        );
    }

    /**
     * @param mixed $value
     */
    private static function maybeSchema(mixed $value): ?self
    {
        if ($value === null) {
            return null;
        }
        if ($value instanceof self) {
            return $value;
        }
        if (is_array($value)) {
            return self::from($value);
        }
        return null;
    }

    /**
     * @param mixed $value
     * @return list<self>|null
     */
    private static function listOfSchemas(mixed $value): ?array
    {
        if (!is_array($value)) {
            return null;
        }
        return array_values(array_map(self::from(...), $value));
    }

    /**
     * @param mixed $value
     * @return array<string, self>|null
     */
    private static function mapOfSchemas(mixed $value): ?array
    {
        if (!is_array($value)) {
            return null;
        }
        $out = [];
        foreach ($value as $k => $v) {
            $out[(string)$k] = self::from($v);
        }
        return $out;
    }

    /**
     * @param mixed $value
     * @return DataType|list<DataType>|null
     */
    private static function parseType(mixed $value): DataType|array|null
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value) && array_is_list($value)) {
            return array_map(DataType::from(...), $value);
        }

        return DataType::from($value);
    }

    /**
     * @param string|null $_id
     * @param string|null $_comment
     * @param array<string, self>|null $_defs
     * @param string|null $_dynamicAnchor
     * @param string|null $_dynamicRef
     * @param string|null $_ref
     * @param string|null $_schema
     * @param string|null $_vocabulary
     * @param bool $additionalProperties
     * @param list<self>|null $allOf
     * @param list<self>|null $anyOf
     * @param mixed $const
     * @param self|null $contains
     * @param string|null $contentEncoding
     * @param string|null $contentMediaType
     * @param string|null $contentSchema
     * @param mixed|null $default
     * @param string|null $dependentRequired
     * @param array<string, self>|null $dependentSchemas
     * @param bool $deprecated
     * @param string|null $description
     * @param self|null $else
     * @param list<mixed>|null $enum
     * @param string|null $examples
     * @param string|null $exclusiveMaximum
     * @param string|null $exclusiveMinimum
     * @param string|null $format
     * @param self|null $if
     * @param self|false|null $items
     * @param string|null $maximum
     * @param string|null $maxItems
     * @param string|null $maxLength
     * @param string|null $maxProperties
     * @param string|null $minimum
     * @param string|null $minItems
     * @param string|null $minLength
     * @param string|null $minProperties
     * @param int|float|null $multipleOf
     * @param self|null $not
     * @param list<self>|null $oneOf
     * @param string|null $pattern
     * @param array<string, self>|null $patternProperties
     * @param list<self>|null $prefixItems
     * @param array<string, self>|null $properties
     * @param array<string, self>|null $propertyNames
     * @param bool $readOnly
     * @param list<string>|null $required
     * @param self|null $then
     * @param string|null $title
     * @param DataType|list<DataType>|null $type
     * @param bool|null $unevaluatedItems
     * @param bool|null $unevaluatedProperties
     * @param string|null $uniqueItems
     * @param bool $writeOnly
     */
    public function __construct(
        public ?string $_id = null,
        public ?string $_comment = null,
        public ?array $_defs = null,
        public ?string $_dynamicAnchor = null,
        public ?string $_dynamicRef = null,
        public ?string $_ref = null,
        public ?string $_schema = null,
        public ?string $_vocabulary = null,
        public bool $additionalProperties = false,
        public ?array $allOf = null,
        public ?array $anyOf = null,
        public mixed $const = null,
        public ?self $contains = null,
        public ?string $contentEncoding = null,
        public ?string $contentMediaType = null,
        public ?string $contentSchema = null,
        public mixed $default = null,
        public ?string $dependentRequired = null,
        public ?array $dependentSchemas = null,
        public bool $deprecated = false,
        public ?string $description = null,
        public ?self $else = null,
        public ?array $enum = null,
        public ?string $examples = null,
        public ?string $exclusiveMaximum = null,
        public ?string $exclusiveMinimum = null,
        public ?string $format = null,
        public ?self $if = null,
        public self|false|null $items = null,
        public ?string $maximum = null,
        public ?string $maxItems = null,
        public ?string $maxLength = null,
        public ?string $maxProperties = null,
        public ?string $minimum = null,
        public ?string $minItems = null,
        public ?string $minLength = null,
        public ?string $minProperties = null,
        public int|float|null $multipleOf = null,
        public ?self $not = null,
        public ?array $oneOf = null,
        public ?string $pattern = null,
        public ?array $patternProperties = null,
        public ?array $prefixItems = null,
        public ?array $properties = null,
        public ?array $propertyNames = null,
        public bool $readOnly = false,
        public ?array $required = null,
        public ?self $then = null,
        public ?string $title = null,
        public DataType|array|null $type = null,
        public ?bool $unevaluatedItems = null,
        public ?bool $unevaluatedProperties = null,
        public ?string $uniqueItems = null,
        public bool $writeOnly = false,
    ) {
    }
}
