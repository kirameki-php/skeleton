<?php declare(strict_types=1);

namespace Kirameki\Framework\Model\Casts;

use BackedEnum;
use Kirameki\Framework\Model\Model;

/**
 * @template T of BackedEnum
 */
class EnumCast implements Cast
{
    /**
     * @var class-string<T>
     */
    public string $class;

    /**
     * @param class-string<T> $class
     */
    public function __construct(string $class)
    {
        $this->class = $class;
    }

    /**
     * @param Model $model
     * @param string $key
     * @param string|int $value
     * @return T
     */
    public function get(Model $model, string $key, mixed $value): BackedEnum
    {
        return $this->class::from($value);
    }
}
