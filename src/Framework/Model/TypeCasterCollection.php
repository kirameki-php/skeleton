<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use BackedEnum;
use Closure;
use Kirameki\Framework\Model\Casts\Cast;
use Kirameki\Framework\Model\Casts\EnumCast;
use LogicException;

class TypeCasterCollection
{
    /**
     * @var array<string, Cast>
     */
    protected array $casters = [];

    /**
     * @param array<string, Closure(string): Cast> $resolvers
     */
    public function __construct(
        protected readonly array $resolvers = [],
    ) {
    }

    /**
     * @param string $name
     * @return Cast
     */
    public function get(string $name): Cast
    {
        return $this->casters[$name] ??= $this->resolve($name);
    }

    /**
     * @param string $name
     * @return Cast
     */
    protected function resolve(string $name): Cast
    {
        if (isset($this->resolvers[$name])) {
            return $this->casters[$name] = $this->resolvers[$name]($name);
        }

        if (enum_exists($name) && is_subclass_of($name, BackedEnum::class)) {
            return $this->casters[$name] = new EnumCast($name);
        }

        throw new LogicException('Unknown cast:' . $name);
    }
}
