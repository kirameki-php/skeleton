<?php declare(strict_types=1);

namespace Kirameki\Framework\Model\Casts;

use Kirameki\Core\Json;
use Kirameki\Framework\Model\Model;
use RuntimeException;
use Traversable;
use function get_debug_type;
use function is_array;
use function is_string;
use function iterator_to_array;

class ArrayCast implements Cast
{
    /**
     * @param Model $model
     * @param string $key
     * @param string $value
     * @return array<array-key, mixed>
     */
    public function get(Model $model, string $key, mixed $value): array
    {
        if (is_string($value)) {
            $value = Json::decode($value);
        }

        if ($value instanceof Traversable) {
            $value = iterator_to_array($value);
        }

        if (is_array($value)) {
            return $value;
        }

        $type = get_debug_type($value);
        throw new RuntimeException("Expected array, {$type} given.");
    }
}
