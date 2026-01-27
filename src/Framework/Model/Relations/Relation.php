<?php declare(strict_types=1);

namespace Kirameki\Framework\Model\Relations;

use Kirameki\Collections\Map;
use Kirameki\Collections\Vec;
use Kirameki\Framework\Model\Model;
use Kirameki\Framework\Model\ModelManager;
use Kirameki\Framework\Model\ModelQueryResult;
use Kirameki\Framework\Model\TableInfo;
use Kirameki\Framework\Model\QueryBuilder;

/**
 * @template TSrc of Model
 * @template TDst of Model
 */
abstract class Relation
{
    /**
     * @var ModelManager
     */
    protected ModelManager $manager;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var TableInfo<TSrc>
     */
    protected TableInfo $srcReflection;

    /**
     * @var TableInfo<TDst>|null
     */
    protected ?TableInfo $dstReflection;

    /**
     * @var class-string<TDst>
     */
    protected string $dstClass;

    /**
     * @var Map<string, string>
     */
    protected Map $keyPairs;

    /**
     * @var string|null
     */
    protected ?string $inverse;

    /**
     * @param ModelManager $manager
     * @param string $name
     * @param TableInfo<TSrc> $srcReflection
     * @param class-string<TDst> $dstClass
     * @param array<string, string> $keyPairs should look like [$srcKeyName => $dstKeyName, ...]
     * @param string|null $inverse
     */
    public function __construct(ModelManager $manager, string $name, TableInfo $srcReflection, string $dstClass, ?array $keyPairs = null, ?string $inverse = null)
    {
        $this->manager = $manager;
        $this->name = $name;
        $this->srcReflection = $srcReflection;
        $this->dstReflection = null;
        $this->dstClass = $dstClass;
        $this->keyPairs = new Map($keyPairs ?: $this->guessKeyPairs());
        $this->inverse = $inverse;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Map<string, string>
     */
    public function getKeyPairs(): Map
    {
        return $this->keyPairs;
    }

    /**
     * @return array<string, string>
     */
    abstract protected function guessKeyPairs(): array;

    /**
     * @return TableInfo<TSrc>
     */
    public function getSrcReflection(): TableInfo
    {
        return $this->srcReflection;
    }

    /**
     * @return Vec<string>
     */
    public function getSrcKeyNames(): Vec
    {
        return $this->getKeyPairs()->keys();
    }

    /**
     * @param TSrc $srcModel
     * @return Vec<mixed>
     */
    protected function getSrcKeys(Model $srcModel): Vec
    {
        return $this->getSrcKeyNames()->map(static fn(string $name) => $srcModel->getProperty($name));
    }

    /**
     * @return TableInfo<TDst>
     */
    public function getDstReflection(): TableInfo
    {
        return $this->dstReflection ??= $this->manager->getTableInfo($this->dstClass);
    }

    /**
     * @return Vec<string>
     */
    public function getDstKeyNames(): Vec
    {
        return $this->getKeyPairs()->values();
    }

    /**
     * @param Model $model
     * @return Vec<mixed>
     */
    protected function getDstKeys(Model $model): Vec
    {
        return $this->getDstKeyNames()->map(static fn(string $name) => $model->getProperty($name));
    }

    /**
     * @return string|null
     */
    public function getInverseName(): ?string
    {
        return $this->inverse;
    }

    /**
     * @param iterable<int, TSrc> $srcModels
     * @return ModelQueryResult<TDst>
     */
    public function load(iterable $srcModels): ModelQueryResult
    {
        $srcModels = $this->srcModelsToCollection($srcModels);
        $dstModels = $this->getDstModels($srcModels);

        $keyedSrcModels = $srcModels->keyBy(fn(Model $model) => $this->getSrcKeys($model)->join('|'));
        $dstModelGroups = $dstModels->groupBy(fn(Model $model) => $this->getDstKeys($model)->join('|'));

        foreach ($dstModelGroups as $key => $groupedDstModels) {
            $this->setDstToSrc($keyedSrcModels[$key], $groupedDstModels);
        }

        return $dstModels;
    }

    /**
     * @param iterable<int, TSrc> $srcModels
     * @return ModelQueryResult<TSrc>
     */
    protected function srcModelsToCollection(iterable $srcModels): ModelQueryResult
    {
        if ($srcModels instanceof ModelQueryResult) {
            return $srcModels;
        }

        if ($srcModels instanceof Vec) {
            $srcModels = $srcModels->all();
        }

        return new ModelQueryResult($this->getSrcReflection(), $srcModels);
    }

    /**
     * @param ModelQueryResult<TSrc> $srcModels
     * @return ModelQueryResult<TDst>
     */
    protected function getDstModels(ModelQueryResult $srcModels): ModelQueryResult
    {
        $query = $this->newQuery();
        $this->addConstraintsToQuery($query, $srcModels);
        return new ModelQueryResult($this->getDstReflection(), $query->execute());
    }

    /**
     * @return QueryBuilder<TDst>
     */
    protected function newQuery(): QueryBuilder
    {
        return new QueryBuilder($this->manager->getDb(), $this->getDstReflection());
    }

    /**
     * @param QueryBuilder<TDst> $query
     * @param ModelQueryResult<TSrc> $srcModels
     * @return void
     */
    protected function addConstraintsToQuery(QueryBuilder $query, ModelQueryResult $srcModels): void
    {
        foreach ($this->keyPairs as $srcName => $dstName) {
            $srcKeys = $srcModels->pluck($srcName)->filter(fn($v) => $v !== null);
            $query->where($dstName, $srcKeys);
        }
    }

    /**
     * @param TSrc $srcModel
     * @param ModelQueryResult<TDst> $dstModels
     * @return void
     */
    abstract protected function setDstToSrc(Model $srcModel, ModelQueryResult $dstModels): void;
}
