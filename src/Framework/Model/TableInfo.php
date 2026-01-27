<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Closure;
use Kirameki\Framework\Model\Relations\Relation;

/**
 * @template TModel of Model
 */
class TableInfo
{
    /**
     * @param class-string<TModel> $class
     * @param string $connection
     * @param string $table
     * @param list<string> $primaryKeys
     * @param array<string, ColumnInfo> $columns
     * @param array<string, Relation<Model, Model>> $relations
     */
    public function __construct(
        public readonly string $class,
        public readonly string $connection,
        public readonly string $table,
        public readonly array  $primaryKeys,
        public readonly array  $columns = [],
        public readonly array  $relations = [],
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
