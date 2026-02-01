<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use BackedEnum;
use Closure;
use Kirameki\Framework\Model\Casts\Cast;
use Kirameki\Framework\Model\Casts\EnumCast;
use LogicException;

class ModelManager
{
    /**
     * @var array<string, Cast>
     */
    protected array $casts = [];

    /**
     * @var array<string, Closure(string): Cast>
     */
    protected array $resolvers = [];

    /**
     * @param string $name
     * @return Cast
     */
    public function getCast(string $name): Cast
    {
        if (isset($this->casts[$name])) {
            return $this->casts[$name];
        }

        if (isset($this->resolvers[$name])) {
            return $this->casts[$name] = $this->resolvers[$name]($name);
        }

        if (enum_exists($name) && is_subclass_of($name, BackedEnum::class)) {
            return $this->casts[$name] = new EnumCast($name);
        }

        throw new LogicException('Unknown cast:' .$name);
    }

    /**
     * @param string $name
     * @param Closure(string): Cast $resolver
     * @return $this
     */
    public function setCast(string $name, Closure $resolver): static
    {
        $this->resolvers[$name] = $resolver;
        return $this;
    }
}
