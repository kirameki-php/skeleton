<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

class TableInfo
{
    /**
     * @param string $connection
     * @param string $name
     * @param list<string> $primaryKeys
     * @param array<string, ColumnInfo> $columns
     */
    public function __construct(
        public readonly string $connection,
        public readonly string $name,
        public readonly array  $primaryKeys,
        public readonly array  $columns = [],
    ) {
    }
}
