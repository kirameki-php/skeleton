<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Kirameki\Database\Query\QueryResult;
use Kirameki\Database\Query\Statements\SelectBuilder;
use Kirameki\Database\Query\Statements\SelectStatement;

/**
 * @template TModel of Model
 * @extends SelectBuilder<TModel>
 */
class QueryBuilder extends SelectBuilder
{
    /**
     * @param TModel $model
     */
    public function __construct(
        protected readonly Model $model,
    ) {
        parent::__construct($model->getConnection()->query());
    }

    /**
     * @return QueryResult<SelectStatement, TModel>
     */
    public function execute(): QueryResult
    {
        return $this->hydrate(parent::execute());
    }
    /**
     * @return TModel
     */
    public function first(): mixed
    {
        return $this
            ->hydrate($this->copy()->limit(1)->execute())
            ->first();
    }

    /**
     * @return TModel|null
     */
    public function firstOrNull(): mixed
    {
        return $this
            ->hydrate($this->copy()->limit(1)->execute())
            ->firstOrNull();
    }

    /**
     * @return TModel
     */
    public function single(): mixed
    {
        return $this
            ->hydrate($this->copy()->limit(2)->execute())
            ->single();
    }

    /**
     * @param QueryResult<SelectStatement, TModel> $result
     * @return ModelQueryResult<TModel>
     */
    protected function hydrate(QueryResult $result): ModelQueryResult
    {
        /** @var ModelQueryResult<TModel> */
        return new ModelQueryResult($this->model->reflection, $result);
    }
}
