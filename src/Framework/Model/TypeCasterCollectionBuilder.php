<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Closure;
use Kirameki\Container\Builder;
use Kirameki\Framework\Model\Casts\Cast;

class TypeCasterCollectionBuilder implements Builder
{
    /**
     * @param array<string, Closure(string): Cast> $resolvers
     */
    public function __construct(
        protected array $resolvers = [],
    ) {
    }

    /**
     * @param string $name
     * @param Closure(string): Cast $resolver
     * @return $this
     */
    public function set(string $name, Closure $resolver): static
    {
        $this->resolvers[$name] = $resolver;
        return $this;
    }

    /**
     * @return TypeCasterCollection
     */
    public function build(): TypeCasterCollection
    {
        return new TypeCasterCollection($this->resolvers);
    }
}
