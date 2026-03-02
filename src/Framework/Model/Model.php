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
    protected array $_stored = [];

    /**
     * Stores and caches properties that were resolved.
     * Casting occurs when a value is set or when a value is called though get.
     *
     * @var array<string, mixed>
     */
    protected array $_resolved = [];

    /**
     * Stores initial values for properties that were changed.
     *
     * @var array<string, mixed>
     */
    protected array $_changed = [];

    /**
     * Stores previous value of properties.
     * It will get cleared when the model is saved.
     *
     * @var array<string, mixed>
     */
    protected array $_previous = [];

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
        if (array_key_exists($name, $this->_resolved)) {
            return $this->_resolved[$name];
        }

        $value = array_key_exists($name, $this->_stored)
            ? $this->table->columns[$name]->cast->get($this, $name, $this->_stored[$name])
            : null;

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
        $this->markAsUnsaved($name, $this->getProperty($name));
        $this->setResolvedProperty($name, $value);
        return $value;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    protected function setResolvedProperty(string $name, mixed $value): void
    {
        $this->_resolved[$name] = $value;
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
     * @internal
     * @param array<string, mixed> $properties
     * @return $this
     */
    public function setStoredProperties(array $properties): static
    {
        $this->_stored = $properties;
        $this->_state = ModelState::Stored;
        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getInitialProperty(string $name): mixed
    {
        return array_key_exists($name, $this->_changed)
            ? $this->_changed[$name]
            : $this->getProperty($name);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getPreviousProperty(string $name): mixed
    {
        return $this->_previous[$name] ?? null;
    }

    /**
     * @param string $name
     * @param mixed $oldValue
     * @return void
     */
    protected function markAsUnsaved(string $name, mixed $oldValue): void
    {
        if (!array_key_exists($name, $this->_changed)) {
            $this->_changed[$name] = $oldValue;
        }

        if (!array_key_exists($name, $this->_previous)) {
            $this->_previous[$name] = $oldValue;
        }

        $this->_state = ModelState::Dirty;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isDirty(string $name): bool
    {
        return count($this->_previous) > 0;
    }

    /**
     * @return bool
     */
    public function hasDirty(): bool
    {
        return count($this->_previous) > 0;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getUnsavedProperties(): array
    {
        $props = [];
        foreach ($this->_previous as $name => $_) {
            $props[$name] = $this->getProperty($name);
        }
        return $props;
    }

    /**
     * @return void
     */
    protected function setUnsavedPropertiesAsPersisted(): void
    {
        foreach($this->getUnsavedProperties() as $name => $value) {
            $this->_stored[$name] = $value;
        }
    }

    /**
     * @return $this
     */
    protected function clearUnsavedProperties(): static
    {
        $this->_previous = [];

        $this->_state = $this->isNewRecord()
            ? ModelState::New
            : ModelState::Stored;

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function wasChanged(string $name): bool
    {
        return array_key_exists($name, $this->_changed);
    }

    /**
     * @return bool
     */
    public function hasChanges(): bool
    {
        return count($this->_changed) > 0;
    }
}
