<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Closure;
use Kirameki\Database\DatabaseManager;
use Kirameki\Framework\Model\Casts\Cast;
use LogicException;

class ModelManager
{
    /**
     * @var DatabaseManager
     */
    protected DatabaseManager $databaseManager;

    /**
     * @var array<class-string, ModelReflection<Model>>
     */
    protected array $reflections;

    /**
     * @var array<string, Cast>
     */
    protected array $casts = [];

    /**
     * @var array<string, Closure(string): Cast>
     */
    protected array $resolvers = [];

    /**
     * @param DatabaseManager $databaseManager
     */
    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * @return DatabaseManager
     */
    public function getDatabaseManager(): DatabaseManager
    {
        return $this->databaseManager;
    }

    /**
     * @template T of Model
     * @param class-string<T> $class
     * @return ModelReflection<T>
     */
    public function reflect(string $class): ModelReflection
    {
        return $this->reflections[$class] ??= $class::getReflection(); /** @phpstan-ignore-line */
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
