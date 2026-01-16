<?php declare(strict_types=1);

namespace Kirameki\Framework\Model\Casts;

use DateTime;
use DateTimeImmutable;
use Kirameki\Framework\Model\Model;
use Kirameki\Time\Instant;
use Stringable;

class InstantCast implements Cast
{
    /**
     * @param Model $model
     * @param string $key
     * @param scalar|Stringable $value
     * @return Instant
     */
    public function get(Model $model, string $key, mixed $value): Instant
    {
        return new Instant(new DateTimeImmutable($value));
    }
}
