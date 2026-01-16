<?php declare(strict_types=1);

namespace Kirameki\Framework\Model\Casts;

use Kirameki\Framework\Model\Model;

class FloatCast implements Cast
{
    /**
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @return float
     */
    public function get(Model $model, string $key, mixed $value): float
    {
        return (float) $value;
    }
}
