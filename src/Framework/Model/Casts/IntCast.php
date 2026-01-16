<?php declare(strict_types=1);

namespace Kirameki\Framework\Model\Casts;

use Kirameki\Framework\Model\Model;

class IntCast implements Cast
{
    /**
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @return int
     */
    public function get(Model $model, string $key, mixed $value): int
    {
        return (int) $value;
    }
}
