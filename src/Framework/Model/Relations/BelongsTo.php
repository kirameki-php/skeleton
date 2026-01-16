<?php declare(strict_types=1);

namespace Kirameki\Framework\Model\Relations;

use Kirameki\Framework\Model\Model;
use Kirameki\Framework\Model\ModelVec;

/**
 * @template TSrc of Model
 * @template TDst of Model
 * @extends Relation<TSrc, TDst>
 */
class BelongsTo extends Relation
{
    /**
     * @return array<string, string>
     */
    protected function guessKeyPairs(): array
    {
        $srcKeyName = $this->getDstReflection()->primaryKey;
        $dstKeyName = lcfirst(class_basename($this->getDstReflection()->class)).'Id';
        return [$srcKeyName => $dstKeyName];
    }

    /**
     * @param TSrc $srcModel
     * @param ModelVec<TDst> $dstModels
     * @return void
     */
    protected function setDstToSrc(Model $srcModel, ModelVec $dstModels): void
    {
        $srcModel->setRelation($this->getName(), $dstModels[0]);
    }
}
