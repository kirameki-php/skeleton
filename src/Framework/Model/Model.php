<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Kirameki\Database\Query\QueryResult;

abstract class Model
{
    use Persistence;
    use Properties;
    use DatabaseInfo;

    /**
     * @param ModelManager $manager
     */
    public function __construct(
        protected ModelManager $manager,
    ) {
    }

    /**
     * @param QueryResult $result
     * @return ModelVec<static>
     */
    public function newVecFromQuery(QueryResult $result): ModelVec
    {
        return new ModelVec(static::getReflection(), $result);
    }

    /**
     * @param array<string, mixed> $row
     * @return static
     */
    protected function newFromQueryRow(array $row): static
    {
        $model = new static($this->manager);
        $model->_persisted = true;
        $model->setPersistedProperties($row);
        return $model;
    }
}
