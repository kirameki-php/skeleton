<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Closure;
use Kirameki\Database\DatabaseConnection;
use Kirameki\Exceptions\RuntimeException;
use Throwable;

/**
 * @template TModel of Model
 */
trait Recordable
{
    /**
     * @var ModelState
     */
    protected ModelState $_state = ModelState::New;

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
     * @param Closure(DatabaseConnection): mixed $callback
     */
    protected function processing(Closure $callback): void
    {
        $current = $this->_state;
        try {
            if ($this->_state !== ModelState::Processing) {
                $this->_state = ModelState::Processing;
                $callback($this->getConnection());
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