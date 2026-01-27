<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Kirameki\App\Models\User;
use Kirameki\Database\DatabaseManager;
use Kirameki\Framework\Model\Attributes\Table;
use ReflectionClass;
use ReflectionProperty;

class ModelFactory
{
    public function __construct(
        protected readonly ModelManager $casts,
        protected readonly DatabaseManager $db,
    ) {
    }

    /**
     * @return QueryBuilder<User>
     */
    public function users(): QueryBuilder
    {
        $this->users()->first()->id;

        return new QueryBuilder(
            new User($this->db, $this->getTableInfo(User::class))
        );
    }


    /**
     * @template TModel of Model
     * @param class-string<TModel> $class
     * @return TableInfo
     */
    protected function getTableInfo(string $class): TableInfo
    {
        $classRef = new ReflectionClass($class);
        $table = $classRef->getAttributes(Table::class)[0] ?? null;

        $columns = [];
        foreach ($classRef->getProperties(ReflectionProperty::IS_PUBLIC) as $ref) {
            $columns[$ref->name] = ColumnInfo::fromReflection($this->casts, $ref);
        }

        return new TableInfo(
            $table->connection ?? 'default',
            $table->name ?? $classRef->getShortName(),
            [],
            $columns,
        );
    }
}
