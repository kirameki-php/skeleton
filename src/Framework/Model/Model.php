<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Kirameki\Database\DatabaseManager;

/**
 * @consistent-constructor
 */
abstract class Model
{
    /** @use Recordable<static> */
    use Recordable;

    /**
     * This has raw values that come directly from the database.
     * For example, the datetime values are stored as string
     * and will not cast until it is actually used.
     *
     * @var array<string, mixed>
     */
    protected array $_persistedProperties = [];

    /**
     * Stores and caches properties that were resolved.
     * Casting occurs when a value is set or when a value is called though get.
     *
     * @var array<string, mixed>
     */
    protected array $_resolvedProperties = [];

    /**
     * Stores initial values for properties that were changed.
     *
     * @var array<string, mixed>
     */
    protected array $_changedProperties = [];

    /**
     * Stores previous value of properties.
     * It will get cleared when the model is saved.
     *
     * @var array<string, mixed>
     */
    protected array $_previousProperties = [];

    /**
     * @var bool
     */
    public protected(set) bool $_persisted = false;

    /**
     * @param DatabaseManager $db
     * @param TableInfo $table
     */
    public function __construct(
        protected readonly DatabaseManager $db,
        public readonly TableInfo $table,
    ) {
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getProperty(string $name): mixed
    {
        if (array_key_exists($name, $this->_resolvedProperties)) {
            return $this->_resolvedProperties[$name];
        }
        $value = $this->getPersistedProperty($name);
        $this->setResolvedProperty($name, $value);
        return $value;
    }

    /**
     * @template T
     * @param string $name
     * @param T $value
     * @return T
     */
    protected function setProperty(string $name, mixed $value): mixed
    {
        $this->markAsDirty($name, $this->getProperty($name));
        $this->setResolvedProperty($name, $value);
        return $value;
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function getPersistedProperty(string $name): mixed
    {
        return array_key_exists($name, $this->_persistedProperties)
            ? $this->getCast($name)->get($this, $name, $this->_persistedProperties[$name])
            : null;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    protected function setResolvedProperty(string $name, mixed $value): void
    {
        $this->_resolvedProperties[$name] = $value;
    }

    /**
     * @return list<string>
     */
    public function getPropertyNames(): array
    {
        return array_keys($this->table->columns);
    }

    /**
     * @return array<string, mixed>
     */
    public function getProperties(): array
    {
        /** @var array<string, mixed> $properties */
        $properties = [];
        foreach ($this->getPropertyNames() as $name) {
            $properties[$name] = $this->getProperty($name);
        }
        return $properties;
    }

    /**
     * @param array<string, mixed> $properties
     * @return $this
     */
    public function setProperties(array $properties = []): static
    {
        foreach ($properties as $name => $value) {
            $this->setProperty($name, $value);
        }
        return $this;
    }

    /**
     * @param array<string, mixed> $properties
     * @return $this
     */
    public function setPersistedProperties(array $properties): static
    {
        $this->_persistedProperties = $properties;
        $this->_persisted = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function clearResolvedProperties(): static
    {
        $this->_resolvedProperties = [];
        return $this;
    }

    /**
     * @param string|null $name
     * @return bool
     */
    public function isResolved(?string $name = null): bool
    {
        return $name !== null
            ? array_key_exists($name, $this->_resolvedProperties)
            : !empty($this->_resolvedProperties);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getInitialProperty(string $name): mixed
    {
        return array_key_exists($name, $this->_changedProperties)
            ? $this->_changedProperties[$name]
            : $this->getProperty($name);
    }

    /**
     * @return array<string, mixed>
     */
    public function getInitialProperties(): array
    {
        $props = [];
        foreach ($this->getPropertyNames() as $name) {
            $props[$name] = $this->getInitialProperty($name);
        }
        return $props;
    }

    /**
     * @param string $name
     * @param mixed $oldValue
     * @return $this
     */
    protected function markAsDirty(string $name, mixed $oldValue): static
    {
        if (!array_key_exists($name, $this->_changedProperties)) {
            $this->_changedProperties[$name] = $oldValue;
        }
        $this->_previousProperties[$name] = $oldValue;
        return $this;
    }

    /**
     * @param string|null $name
     * @return bool
     */
    public function isDirty(?string $name = null): bool
    {
        return $name !== null
            ? array_key_exists($name, $this->_previousProperties)
            : !empty($this->_previousProperties);
    }

    /**
     * @return array<string, mixed>
     */
    public function getDirtyProperties(): array
    {
        $props = [];
        foreach ($this->_previousProperties as $name => $_) {
            $props[$name] = $this->getProperty($name);
        }
        return $props;
    }

    /**
     * @return void
     */
    protected function setDirtyPropertiesAsPersisted(): void
    {
        foreach($this->_previousProperties as $name => $value) {
            $this->_persistedProperties[$name] = $value;
        }
    }

    /**
     * @return $this
     */
    protected function clearDirtyProperties(): static
    {
        $this->_previousProperties = [];
        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getPreviousProperty(string $name): mixed
    {
        return $this->_previousProperties[$name] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getPreviousProperties(): array
    {
        return $this->_previousProperties;
    }

    /**
     * @param string|null $name
     * @return bool
     */
    public function wasChanged(?string $name = null): bool
    {
        return $name !== null
            ? array_key_exists($name, $this->_changedProperties)
            : count($this->_changedProperties) > 0;
    }

    /**
     * @return $this
     */
    public function clearChanges(): static
    {
        $this->_changedProperties = [];
        $this->_previousProperties = [];
        return $this;
    }
}
