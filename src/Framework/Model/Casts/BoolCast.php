<?php declare(strict_types=1);

namespace Kirameki\Framework\Model\Casts;

use Kirameki\Framework\Model\Model;

class BoolCast implements Cast
{
    /**
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function get(Model $model, string $key, mixed $value): bool
    {
        return (bool) $value;
    }
}
