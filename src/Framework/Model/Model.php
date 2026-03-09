<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Kirameki\Database\DatabaseConnection;
use Kirameki\Database\DatabaseManager;
use Kirameki\Exceptions\RuntimeException;
use Closure;
use Throwable;

/**
 * @consistent-constructor
 */
abstract class Model
{
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
     * Database manager is used to get the connection for the model.
     * @param TableInfo $table
     * The table info contains the column definitions and primary key information.
     * @param array<string, mixed> $_stored
     * This has raw values that come directly from the database.
     * For example, the datetime values are stored as string
     * and will not cast until it is actually used.
     * @param ModelState $_state
     * Model state is used to determine if the record is new, dirty, stored, or deleted.
     */
    public function __construct(
        protected readonly DatabaseManager $db,
        public readonly TableInfo $table,
        protected array $_stored = [],
        protected ModelState $_state = ModelState::New,
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

    /**
     * @return bool
     */
    public function isNewRecord(): bool
    {
        return $this->_state === ModelState::New;
    }

    /**
     * @return bool
     */
    public function isStored(): bool
    {
        return $this->_state === ModelState::Stored;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->_state === ModelState::Deleted;
    }

    /**
     * @return DatabaseConnection
     */
    public function getConnection(): DatabaseConnection
    {
        return $this->db->use($this->table->connection);
    }

    /**
     * @return list<array-key>
     */
    public function getPrimaryKeys(): array
    {
        $keys = [];
        foreach ($this->table->primaryKeys as $name) {
            $keys[] = $this->getProperty($name);
        }
        return $keys;
    }

    /**
     * @return $this
     */
    public function save(): static
    {
        if ($this->isDeleted()) {
            throw new RuntimeException(sprintf('Trying to save record which was deleted! (%s:%s)',
                $this->table->name,
                implode(', ', $this->table->primaryKeys)),
            );
        }

        $this->processing(function() {
            $this->isNewRecord()
                ? $this->insertProperties()
                : $this->updateProperties();
            $this->setUnsavedPropertiesAsPersisted();
            $this->clearUnsavedProperties();
        });

        $this->_state = ModelState::Stored;

        return $this;
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        if ($this->isDeleted()) {
            return false;
        }

        // trying to delete a record with dirty primary key is dangerous.
        foreach ($this->table->primaryKeys as $primaryKeyName) {
            if ($this->isDirty($primaryKeyName)) {
                throw new RuntimeException('Deleting a record with dirty primary key is not allowed.'); // TODO Better exception handling
            }
        }

        $deleted = false;
        $this->processing(function() use (&$deleted) {
            $query = $this->getConnection()->query()->deleteFrom($this->table->name);
            foreach ($this->table->primaryKeys as $primaryKeyName) {
                $query->where($primaryKeyName, $this->getProperty($primaryKeyName));
            }
            $result = $query->execute();

            if ($result->affectedRowCount > 0) {
                $deleted = true;
            }
        });

        if ($deleted) {
            $this->_state = ModelState::Deleted;
        }

        return $deleted;
    }

    /**
     * @param Closure(): mixed $callback
     */
    protected function processing(Closure $callback): void
    {
        $current = $this->_state;
        try {
            if ($this->_state !== ModelState::Processing) {
                $this->_state = ModelState::Processing;
                $callback();
            }
        }
        catch (Throwable $e) {
            $this->_state = $current;
            throw $e;
        }
    }

    /**
     * @return void
     */
    protected function insertProperties(): void
    {
        $this->getConnection()->query()
            ->insertInto($this->table->name)
            ->value($this->getPropertiesForInsert())
            ->execute();
    }

    /**
     * @return void
     */
    protected function updateProperties(): void
    {
        $this->getConnection()->query()
            ->update($this->table->name)
            ->set($this->getPropertiesForUpdate())
            ->execute();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getPropertiesForInsert(): array
    {
        return $this->getProperties();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getPropertiesForUpdate(): array
    {
        return $this->getUnsavedProperties();
    }
}
