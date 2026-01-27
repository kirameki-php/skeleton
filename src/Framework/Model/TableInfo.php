<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Kirameki\Framework\Model\Relations\Relation;

class TableInfo
{
    /**
     * @param string $connection
     * @param string $name
     * @param list<string> $primaryKeys
     * @param array<string, ColumnInfo> $columns
     * @param array<string, Relation<Model, Model>> $relations
     */
    public function __construct(
        public readonly string $connection,
        public readonly string $name,
        public readonly array  $primaryKeys,
        public readonly array  $columns = [],
        public readonly array  $relations = [],
    ) {
    }
}
