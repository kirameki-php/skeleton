<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Kirameki\Database\Query\QueryResult;
use Kirameki\Database\Query\Statements\SelectStatement;

/**
 * @template TModel of Model
 * @extends QueryResult<SelectStatement, TModel>
 */
class ModelQueryResult extends QueryResult
{
    /**
     * @var ModelReflection<TModel>
     */
    protected ModelReflection $reflection;

    /**
     * @param ModelReflection<TModel> $reflection
     * @param QueryResult<SelectStatement, TModel> $result
     */
    public function __construct(ModelReflection $reflection, QueryResult $result)
    {
        parent::__construct(
            $result->statement,
            $result->template,
            $result->parameters,
            $result->elapsedMs,
            $result->affectedRowCount,
            $result->items,
        );

        $this->reflection = $reflection;
    }

    /**
     * @param Model $items
     * @return static
     */
    public function newInstance(mixed $items): static
    {
        return new static($this->reflection, $items);
    }

    /**
     * @param array<string, mixed> $properties
     * @return Model
     */
    public function make(array $properties = []): Model
    {
        $model = $this->reflection->makeModel($properties);
        $this->append($model);
        return $model;
    }

    /**
     * @param array<string, mixed> $row
     * @return TModel
     */
    protected function newFromQueryRow(array $row): Model
    {
        $model = $this->reflection->makeModel();
        $model->setPersistedProperties($row);
        return $model;
    }
}
