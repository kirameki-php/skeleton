<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Kirameki\Collections\Utils\Arr;

trait Relations
{
    /**
     * @var array<Model|RelationCollection<static, Model>>
     */
    protected array $relations;

    /**
     * @return array<Model|RelationCollection<static, Model>>
     */
    public function getRelations(): array
    {
        return $this->relations ?? [];
    }

    /**
     * @param string $name
     * @return Model|RelationCollection<static, Model>|null
     */
    public function getRelation(string $name): Model|RelationCollection|null
    {
        if (!isset($this->relations)) {
            return null;
        }

        if (!array_key_exists($name, $this->relations)) {
            $this->loadRelation([$this], $name);
        }

        return $this->relations[$name];
    }

    /**
     * @param string $name
     * @param Model|RelationCollection<static, Model> $models
     * @return $this
     */
    public function setRelation(string $name, Model|RelationCollection $models): static
    {
        $this->relations ??= [];
        $this->relations[$name] = $models;
        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isRelation(string $name): bool
    {
        return isset(static::getReflection()->relations[$name]);
    }

    /**
     * @template TModel of Model
     * @param Model $srcModels
     * @param string $name
     * @return ModelQueryResult<int, Model>
     */
    protected function loadRelation(iterable $srcModels, string $name): ModelQueryResult
    {
        return static::getReflection()->relations[$name]->load($srcModels);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isRelationLoaded(string $name): bool
    {
        return isset($this->relations) && array_key_exists($name, $this->relations);
    }

    /**
     * @param string|array<string> $relationNames
     * @return $this
     */
    public function preload(string|array $relationNames): static
    {
        return $this->preloadRecursive([$this], (array)($relationNames));
    }

    /**
     * @template TModel of Model
     * @param Model $target
     * @param array<array-key, string> $names
     * @return $this
     */
    protected function preloadRecursive(iterable $target, array $names): static
    {
        if (Arr::isList($names)) {
            foreach ($names as $name) {
                $this->loadRelation($target, $name);
            }
        } else {
            foreach ($names as $name => $inner) {
                $models = $this->loadRelation($target, $name);
                $this->preloadRecursive($models, (array)($inner));
            }
        }
        return $this;
    }
}
