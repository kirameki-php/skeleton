<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Kirameki\Collections\Map;
use Kirameki\Collections\Utils\Arr;
use Kirameki\Collections\Vec;

/**
 * @template TModel of Model
 * @extends Vec<TModel>
 */
class ModelVec extends Vec
{
    /**
     * @var ModelReflection<TModel>
     */
    protected ModelReflection $reflection;

    /**
     * @param ModelReflection<TModel> $reflection
     * @param Model $models
     */
    public function __construct(ModelReflection $reflection, iterable $models = [])
    {
        parent::__construct($models);
        $this->reflection = $reflection;
    }

    /**
     * @param Model $items
     * @return static
     */
    public function newInstance(mixed $items): static
    {
        return new static($this->reflection, $items);
    }

    /**
     * @return ModelReflection<TModel>
     */
    public function getReflection(): ModelReflection
    {
        return $this->reflection;
    }

    /**
     * @param array-key $key
     * @return Vec<mixed>
     */
    public function pluck(int|string $key): Vec
    {
        return $this->newVec(Arr::map($this->items, $key));
    }

    /**
     * @return Vec<int|string>
     */
    public function primaryKeys(): Vec
    {
        return $this->pluck($this->reflection->primaryKey);
    }

    /**
     * @return Map<array-key, TModel>
     */
    public function keyByPrimaryKey(): static
    {
        return $this->keyBy(fn(Model $m) => $m->getProperty($this->reflection->primaryKey));
    }

    /**
     * @param array<string, mixed> $properties
     * @return Model
     */
    public function make(array $properties = []): Model
    {
        $model = $this->reflection->makeModel($properties);
        $this->append($model);
        return $model;
    }
}
