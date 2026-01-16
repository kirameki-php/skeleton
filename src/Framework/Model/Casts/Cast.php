<?php declare(strict_types=1);

namespace Kirameki\Framework\Model\Casts;

use Kirameki\Framework\Model\Model;

interface Cast
{
    /**
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function get(Model $model, string $key, mixed $value): mixed;
}
