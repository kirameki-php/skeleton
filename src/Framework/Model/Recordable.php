<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Closure;
use Kirameki\Database\Connection;
use Kirameki\Exceptions\RuntimeException;
use Kirameki\Framework\Model\Casts\Cast;

/**
 * @template TModel of Model
 */
trait Recordable
{
    /**
     * @var bool
     */
    protected bool $_deleted = false;

    /**
     * @var bool
     */
    protected bool $_processing = false;

    /**
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->db->use($this->tableInfo->connection);
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->tableInfo->name;
    }

    /**
     * @param string $name
     * @return Cast
     */
    public function getCast(string $name): Cast
    {
        return $this->tableInfo->columns[$name]->cast;
    }

    /**
     * @return list<string>
     */
    public function getPrimaryKeyNames(): array
    {
        return $this->tableInfo->primaryKeys;
    }

    /**
     * @return list<array-key>
     */
    public function getPrimaryKeys(): array
    {
        return array_map($this->getProperty(...), $this->getPrimaryKeyNames());
    }

    /**
     * @return $this
     */
    public function save(): static
    {
        if ($this->isDeleted()) {
            throw new RuntimeException(sprintf('Trying to save record which was deleted! (%s:%s)',
                $this->getTable(),
                implode(', ', $this->getPrimaryKeyNames())),
            );
        }

        $this->processing(function(Connection $conn) {
            $table = $this->getTable();

            $this->isNewRecord()
                ? $conn->query()->insertInto($table)->value($this->getPropertiesForInsert())->execute()
                : $conn->query()->update($table)->set($this->getPropertiesForUpdate())->execute();

            $this->setDirtyPropertiesAsPersisted();
            $this->clearDirtyProperties();
        });

        return $this;
    }

    /**
     * @return bool
     */
    public function isNewRecord(): bool
    {
        return !$this->_persisted && !$this->_deleted;
    }

    /**
     * @return bool
     */
    public function isPersisted(): bool
    {
        return $this->_persisted && !$this->_deleted;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->_deleted;
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
        foreach ($this->getPrimaryKeyNames() as $primaryKeyName) {
            if ($this->isDirty($primaryKeyName)) {
                throw new RuntimeException('Deleting a record with dirty primary key is not allowed.'); // TODO Better exception handling
            }
        }

        $this->processing(function(Connection $conn) {
            $query = $conn->query()->deleteFrom($this->getTable());

            foreach ($this->getPrimaryKeyNames() as $primaryKeyName) {
                $query->where($primaryKeyName, $this->getProperty($primaryKeyName));
            }
            $result = $query->execute();
            $count = $result->affectedRowCount;

            $this->_deleted = $count > 0;
        });

        return $this->_deleted;
    }

    /**
     * @param Closure(Connection): mixed $callback
     */
    protected function processing(Closure $callback): void
    {
        try {
            if (!$this->_processing) {
                $this->_processing = true;
                $callback($this->getConnection());
            }
        }
        finally {
            $this->_processing = false;
        }
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
        return $this->getDirtyProperties();
    }
}