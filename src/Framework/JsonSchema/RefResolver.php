<?php declare(strict_types=1);

namespace Kirameki\Framework\JsonSchema;

use RuntimeException;

/**
 * Resolves `$ref` values against a root {@see JsonSchema}.
 *
 * Currently supports intra-document JSON Pointer references such as:
 *   - `#`                       (the root schema itself)
 *   - `#/$defs/Foo`
 *   - `#/properties/user`
 *   - `#/allOf/0`
 *   - `#/items`
 *
 * External URIs and `$id`-based anchors are not supported yet.
 */
final class RefResolver
{
    /**
     * @var array<string, JsonSchema>
     */
    private array $resolved = [];

    public function __construct(
        private readonly JsonSchema $root,
    ) {
    }

    /**
     * Resolve a `$ref` against the root schema.
     *
     * @param string $ref
     * @return JsonSchema
     */
    public function resolve(string $ref): JsonSchema
    {
        if (isset($this->resolved[$ref])) {
            return $this->resolved[$ref];
        }

        if ($ref === '' || $ref === '#') {
            return $this->resolved[$ref] = $this->root;
        }

        if (!str_starts_with($ref, '#/')) {
            throw new RuntimeException("Unsupported \$ref: {$ref} (only intra-document JSON Pointers are supported)");
        }

        return $this->resolved[$ref] = $this->followPointer($ref);
    }

    /**
     * @param string $ref
     * @return JsonSchema
     */
    private function followPointer(string $ref): JsonSchema
    {
        $pointer = substr($ref, 1); // drop leading '#'
        $current = $this->root;
        $segments = explode('/', ltrim($pointer, '/'));

        foreach ($segments as $raw) {
            $token = str_replace(['~1', '~0'], ['/', '~'], rawurldecode($raw));
            $current = $this->step($current, $token, $pointer);
        }

        return $current;
    }

    private function step(JsonSchema $node, string $token, string $pointer): JsonSchema
    {
        return match ($token) {
            '$defs' => throw new RuntimeException("\$defs requires a child name in {$pointer}"),
            'properties' => throw new RuntimeException("properties requires a child name in {$pointer}"),
            'patternProperties' => throw new RuntimeException("patternProperties requires a child name in {$pointer}"),
            'dependentSchemas' => throw new RuntimeException("dependentSchemas requires a child name in {$pointer}"),
            'allOf',
            'anyOf',
            'oneOf',
            'prefixItems' => throw new RuntimeException("{$token} requires a numeric index in {$pointer}"),
            'items' => $this->expectSchema($node->items, $token, $pointer),
            'contains' => $this->expectSchema($node->contains, $token, $pointer),
            'not' => $this->expectSchema($node->not, $token, $pointer),
            'if' => $this->expectSchema($node->if, $token, $pointer),
            'then' => $this->expectSchema($node->then, $token, $pointer),
            'else' => $this->expectSchema($node->else, $token, $pointer),
            default => $this->descend($node, $token, $pointer),
        };
    }

    private function descend(JsonSchema $node, string $token, string $pointer): JsonSchema
    {
        // Attempt named-map containers in precedence order.
        foreach ([
            $node->_defs,
            $node->properties,
            $node->patternProperties,
            $node->dependentSchemas,
        ] as $map) {
            if (is_array($map) && array_key_exists($token, $map)) {
                return $map[$token];
            }
        }

        // Attempt numeric-index containers for tokens that look like integers.
        if ($token !== '' && preg_match('/^\d+$/', $token) === 1) {
            $index = (int) $token;
            foreach ([
                $node->allOf,
                $node->anyOf,
                $node->oneOf,
                $node->prefixItems,
            ] as $list) {
                if (is_array($list) && array_key_exists($index, $list)) {
                    return $list[$index];
                }
            }
        }

        throw new RuntimeException("Cannot resolve JSON Pointer segment '{$token}' in {$pointer}");
    }

    private function expectSchema(mixed $value, string $token, string $pointer): JsonSchema
    {
        if (!$value instanceof JsonSchema) {
            throw new RuntimeException("Pointer segment '{$token}' does not refer to a schema in {$pointer}");
        }
        return $value;
    }
}



