<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Kirameki\Exceptions\InvalidArgumentException;
use Kirameki\Framework\Model\Attributes\Column;
use Kirameki\Framework\Model\Casts\Cast;
use ReflectionNamedType;
use ReflectionProperty;

final class ColumnInfo
{
    public static function fromReflection(TypeCasterCollection $casters, ReflectionProperty $ref): self
    {
        $column = $ref->getAttributes(Column::class)[0] ?? null;
        $name = $column->name ?? $ref->name;
        $caster = $casters->get(self::getCastType($ref));
        return new self($name, $caster);
    }

    protected static function getCastType(ReflectionProperty $ref): string
    {
        $type = $ref->getType();

        if (!$type instanceof ReflectionNamedType) {
            throw new InvalidArgumentException("Column attributed must have a single named type.");
        }

        return $type->getName();
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
