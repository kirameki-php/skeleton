<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Closure;
use Kirameki\Database\Query\Statements\SelectBuilder;
use Override;

/**
 * @template TModel of Model
 * @extends SelectBuilder<TModel>
 */
class ModelQueryBuilder extends SelectBuilder
{
    /**
     * @param TModel $model
     * @param Closure(): TModel $generator
     */
    public function __construct(
        public readonly Model $model,
        protected readonly Closure $generator,
    ) {
        parent::__construct($model->getConnection()->query());
    }

    /**
     * @return ModelQueryResult<TModel>
     */
    public function execute(): ModelQueryResult
    {
        return new ModelQueryResult(parent::execute(), $this->generator);
    }

    /**
     * @return SelectBuilder<TModel>
     */
    public function toBase(): SelectBuilder
    {
        /** @noinspection PhpParamsInspection */
        return new SelectBuilder($this->handler, $this->statement);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function count(): int
    {
        return $this->toBase()->count();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function tally(): array
    {
        return $this->toBase()->tally();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function sum(string $column): float|int
    {
        return $this->toBase()->sum($column);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function avg(string $column): float
    {
        return $this->toBase()->avg($column);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function min(string $column): int
    {
        return $this->toBase()->min($column);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function max(string $column): int
    {
        return $this->toBase()->max($column);
    }
}
