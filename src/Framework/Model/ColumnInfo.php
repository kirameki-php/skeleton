<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Kirameki\Exceptions\InvalidArgumentException;
use Kirameki\Framework\Model\Attributes\Column;
use Kirameki\Framework\Model\Casts\Cast;
use ReflectionNamedType;
use ReflectionProperty;

final class ColumnInfo
{
    public static function fromReflection(ReflectionProperty $ref): self
    {
        $column = $ref->getAttributes(Column::class)[0] ?? null;
        $columnName = $column->name ?? $ref->name;

        return new self($columnName, self::resolveCast($ref));
    }

    protected static function resolveCast(ReflectionProperty $ref): Cast
    {
        $type = $ref->getType();

        if (!$type instanceof ReflectionNamedType) {
            throw new InvalidArgumentException("Column attributed must have a single named type.");
        }

        $typeName = $type->getName();
    }

    /**
     * @param string $name
     * @param Cast $cast
     */
    protected function __construct(
        public readonly string $name,
        public readonly Cast $cast,
    ) {
    }
}
