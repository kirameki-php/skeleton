<?php declare(strict_types=1);

namespace Kirameki\Framework\Model\Casts;

use Kirameki\Framework\Model\Model;

class StringCast implements Cast
{
    /**
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @return string
     */
    public function get(Model $model, string $key, mixed $value): string
    {
        return (string) $value;
    }
}
