<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Closure;
use Kirameki\Database\Query\QueryResult;
use Kirameki\Database\Query\Statements\SelectStatement;
use Traversable;

/**
 * @template TModel of Model
 * @extends QueryResult<SelectStatement, TModel>
 * @consistent-constructor
 */
class ModelQueryResult extends QueryResult
{
    /**
     * @param QueryResult<SelectStatement, TModel> $result
     * @param Closure(): TModel $generator
     */
    public function __construct(
        QueryResult $result,
        protected Closure $generator,
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
     * @inheritDoc
     */
    public function getIterator(): Traversable
    {
        foreach (parent::getIterator() as $item) {
            yield $this->newFromQueryRow((array) $item);
        }
    }

    /**
     * @param static $iterable
     * @return static
     */
    public function instantiate(mixed $iterable): static
    {
        return new static($iterable, $this->generator);
    }

    /**
     * @param array<string, mixed> $properties
     * @return TModel
     */
    public function make(array $properties = []): Model
    {
        return $this->newFromQueryRow($properties);
    }

    /**
     * @param array<string, mixed> $row
     * @return TModel
     */
    protected function newFromQueryRow(array $row): Model
    {
        $model = ($this->generator)();
        $model->setPersistedProperties($row);
        return $model;
    }
}
