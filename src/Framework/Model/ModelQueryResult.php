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
     * @param class-string<TModel> $class
     * @param QueryResult<SelectStatement, TModel> $result
     */
    public function __construct(
        protected string $class,
        QueryResult $result,
    ) {
        parent::__construct(
            $result->statement,
            $result->template,
            $result->parameters,
            $result->elapsedMs,
            $result->affectedRowCount,
            $result->items,
        );
    }

    /**
     * @param Model $items
     * @return static
     */
    public function newInstance(mixed $items): static
    {
        return new static($this->class, $items);
    }

    /**
     * @param array<string, mixed> $properties
     * @return Model
     */
    public function make(array $properties = []): Model
    {
        $model = new $this->class($properties);
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
