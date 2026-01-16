<?php declare(strict_types=1);

namespace Kirameki\Framework\Model\Relations;

use Kirameki\Framework\Model\Model;
use Kirameki\Framework\Model\ModelVec;
use Kirameki\Framework\Model\ModelReflection;

/**
 * @template TSrc of Model
 * @template TDst of Model
 * @extends ModelVec<int, TDst>
 */
class RelationVec extends ModelVec
{
    /**
     * @var Relation<TSrc, TDst>
     */
    protected Relation $relation;

    /**
     * @var TSrc
     */
    protected Model $srcModel;

    /**
     * @var iterable<int, TDst>
     */
    protected iterable $items;

    /**
     * @param Relation<TSrc, TDst> $relation
     * @param TSrc $srcModel
     * @param ModelReflection<TDst> $dstReflection
     * @param iterable<int, TDst> $dstModels
     */
    public function __construct(Relation $relation, Model $srcModel, ModelReflection $dstReflection, iterable $dstModels)
    {
        parent::__construct($dstReflection, $dstModels);
        $this->relation = $relation;
        $this->srcModel = $srcModel;
   }

    /**
     * @return Relation<TSrc, TDst>
     */
    public function getRelation(): Relation
    {
        return $this->relation;
    }

    /**
     * @param iterable<int, TDst> $items
     * @return static
     */
    public function newInstance(mixed $items): static
    {
        return new static($this->relation, $this->srcModel, $this->reflection, $items);
    }

    /**
     * @inheritDoc
     */
    public function make(array $properties = []): Model
    {
        return $this->setRelatedKeys(parent::make($properties));
    }

    /**
     * @param array<string, mixed> $properties
     * @return TDst
     */
    public function create(array $properties = []): Model
    {
        return $this->make($properties)->save();
    }

    /**
     * @return void
     */
    public function save(): void
    {
        foreach ($this as $item) {
            $item->save();
        }
    }

    /**
     * @return int
     */
    public function deleteAll(): int
    {
        return $this->count(static fn(?Model $model) => $model !== null && $model->delete());
    }

    /**
     * @template UDst of Model
     * @param UDst $dstModel
     * @return UDst
     */
    protected function setRelatedKeys(Model $dstModel): Model
    {
        foreach ($this->relation->getKeyPairs() as $srcKeyName => $dstKeyName) {
            $srcKey = $this->srcModel->getProperty($srcKeyName);
            $dstModel->setProperty($dstKeyName, $srcKey);
        }
        return $dstModel;
    }
}
