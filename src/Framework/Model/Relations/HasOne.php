<?php declare(strict_types=1);

namespace Kirameki\Framework\Model\Relations;

use Kirameki\Framework\Model\Model;
use Kirameki\Framework\Model\ModelQueryResult;

/**
 * @template TSrc of Model
 * @template TDst of Model
 * @extends Relation<TSrc, TDst>
 */
class HasOne extends Relation
{
    /**
     * @return array<string, string>
     */
    protected function guessKeyPairs(): array
    {
        $srcKeyName = $this->getSrcReflection()->primaryKeys;
        $dstKeyName = lcfirst($this->getSrcReflection()->class->getTable()).'Id';
        return [$srcKeyName => $dstKeyName];
    }

    /**
     * @param TSrc $srcModel
     * @param ModelQueryResult<TDst> $dstModels
     * @return void
     */
    protected function setDstToSrc(Model $srcModel, ModelQueryResult $dstModels): void
    {
        $destModel = $dstModels[0];
        $srcModel->setRelation($this->getName(), $destModel);
        $this->setInverseRelations($srcModel, $destModel);
    }

    /**
     * @param TSrc $srcModel
     * @param TDst $destModel
     * @return void
     */
    protected function setInverseRelations(Model $srcModel, Model $destModel): void
    {
        if ($inverse = $this->getInverseName()) {
            $destModel->setRelation($inverse, $srcModel);
        }
    }
}
