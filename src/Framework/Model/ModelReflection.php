<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Closure;
use Kirameki\Framework\Model\Relations\Relation;

/**
 * @template TModel of Model
 */
class ModelReflection
{
    /**
     * @param class-string<TModel> $class
     * @param string $connectionName
     * @param string $tableName
     * @param string $primaryKey
     * @param array<string, Property> $properties
     * @param array<string, Relation<Model, Model>> $relations
     */
    public function __construct(
        public readonly string $class,
        public readonly string $connectionName,
        public readonly string $tableName,
        public readonly string $primaryKey,
        public readonly array $properties = [],
        public readonly array $relations = [],
    ) {
    }

    /**
     * @param array<string, mixed> $properties
     * @param bool $persisted
     * @return TModel
     */
    public function makeModel(array $properties = [], bool $persisted = false): Model
    {
        return new $this->class($properties, $persisted);
    }
}
