<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Kirameki\App\Models\User;
use Kirameki\Container\Container;
use Kirameki\Database\Connection;
use Kirameki\Database\DatabaseManager;
use Kirameki\Framework\Model\Attributes\Table;
use ReflectionClass;
use ReflectionProperty;

class ModelFactory
{
    public function __construct(
        protected readonly Container $container,
        protected readonly Connection $connection,
        protected readonly ModelManager $casts,
    ) {
    }

    /**
     * @return SelectBuilder<User>
     */
    public function users(): SelectBuilder
    {
        return new SelectBuilder(
            $this->generate(User::class),
            fn() => $this->generate(User::class),
        );
    }

    /**
     * @template TModel of Model
     * @param class-string<TModel> $class
     * @return TModel
     */
    public function generate(string $class): object
    {
        return $this->container->make($class, [
            'db' => $this->container->get(DatabaseManager::class),
            'table' => $this->getTableInfo(User::class),
        ]);
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
