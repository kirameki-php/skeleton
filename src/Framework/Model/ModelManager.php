<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Closure;
use Kirameki\Database\DatabaseManager;
use Kirameki\Framework\Model\Casts\Cast;
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
     * @template T of Model
     * @param class-string<T> $class
     * @return TableInfo<T>
     */
    public function getTableInfo(string $class): TableInfo
    {
        return $class::getTableInfo();
    }

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

        if (enum_exists($name)) {
            return $this->casts[$name] = $this->resolvers['{enum}']($name);
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
