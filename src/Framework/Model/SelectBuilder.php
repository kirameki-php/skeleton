<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Closure;
use Kirameki\Database\Query\Statements\SelectBuilder as BaseSelectBuilder;

/**
 * @template TModel of Model
 * @extends BaseSelectBuilder<TModel>
 */
class SelectBuilder extends BaseSelectBuilder
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
}
