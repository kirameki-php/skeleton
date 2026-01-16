<?php declare(strict_types=1);

namespace Kirameki\Framework\Model\Relations;

use Closure;
use Kirameki\Collections\Map;
use Kirameki\Collections\Vec;
use Kirameki\Framework\Model\Model;
use Kirameki\Framework\Model\ModelManager;
use Kirameki\Framework\Model\ModelVec;
use Kirameki\Framework\Model\ModelReflection;

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
     * @var ModelReflection<TSrc>
     */
    protected ModelReflection $srcReflection;

    /**
     * @var ModelReflection<TDst>|null
     */
    protected ?ModelReflection $dstReflection;

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
     * @var array<int, Closure>
     */
    protected array $scopes;

    /**
     * @param ModelManager $manager
     * @param string $name
     * @param ModelReflection<TSrc> $srcReflection
     * @param class-string<TDst> $dstClass
     * @param array<string, string> $keyPairs should look like [$srcKeyName => $dstKeyName, ...]
     * @param string|null $inverse
     */
    public function __construct(ModelManager $manager, string $name, ModelReflection $srcReflection, string $dstClass, array $keyPairs = null, ?string $inverse = null)
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
     * @return ModelReflection<TSrc>
     */
    public function getSrcReflection(): ModelReflection
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
     * @return ModelReflection<TDst>
     */
    public function getDstReflection(): ModelReflection
    {
        return $this->dstReflection ??= $this->manager->reflect($this->dstClass);
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
     * @param string|Closure(QueryBuilder<TDst>, ModelVec<int, TSrc>): QueryBuilder<TDst> $scope
     * @return $this
     */
    public function scope(string|Closure $scope): static
    {
        $this->scopes[] = is_string($scope)
            ? $this->getDstReflection()->scopes[$scope]
            : $scope;

        return $this;
    }

    /**
     * @param iterable<int, TSrc> $srcModels
     * @return ModelVec<TDst>
     */
    public function load(iterable $srcModels): ModelVec
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
     * @return ModelVec<TSrc>
     */
    protected function srcModelsToCollection(iterable $srcModels): ModelVec
    {
        if ($srcModels instanceof ModelVec) {
            return $srcModels;
        }

        if ($srcModels instanceof Vec) {
            $srcModels = $srcModels->all();
        }

        return new ModelVec($this->srcReflection, $srcModels);
    }

    /**
     * @param ModelVec<TSrc> $srcModels
     * @return ModelVec<TDst>
     */
    protected function getDstModels(ModelVec $srcModels): ModelVec
    {
        $query = $this->newQuery();
        $this->addConstraintsToQuery($query, $srcModels);
        $this->addScopesToQuery($query, $srcModels);
        return $query->all();
    }

    /**
     * @return QueryBuilder<TDst>
     */
    protected function newQuery(): QueryBuilder
    {
        return new QueryBuilder($this->manager->getDatabaseManager(), $this->getDstReflection());
    }

    /**
     * @param QueryBuilder<TDst> $query
     * @param ModelVec<TSrc> $srcModels
     * @return void
     */
    protected function addConstraintsToQuery(QueryBuilder $query, ModelVec $srcModels): void
    {
        foreach ($this->keyPairs as $srcName => $dstName) {
            $srcKeys = $srcModels->pluck($srcName)->filter(fn($v) => $v !== null);
            $query->where($dstName, $srcKeys);
        }
    }

    /**
     * @param QueryBuilder<TDst> $query
     * @param ModelVec<TSrc> $srcModels
     * @return void
     */
    protected function addScopesToQuery(QueryBuilder $query, ModelVec $srcModels): void
    {
        foreach ($this->scopes as $scope) {
            $scope($query, $srcModels);
        }
    }

    /**
     * @param TSrc $srcModel
     * @param ModelVec<TDst> $dstModels
     * @return void
     */
    abstract protected function setDstToSrc(Model $srcModel, ModelVec $dstModels): void;
}
