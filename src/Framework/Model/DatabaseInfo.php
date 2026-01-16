<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Kirameki\Database\Connection;
use Kirameki\Framework\Model\Casts\Cast;

/**
 * @mixin Model
 */
trait DatabaseInfo
{
    /**
     * @var ModelReflection<static>|null
     */
    protected static ?ModelReflection $reflection = null;

    /**
     * @return ModelReflection<static>
     */
    public static function getReflection(): ModelReflection
    {
        return static::$reflection ??= static::resolveReflection();
    }

    /**
     * @return ModelReflection<static>
     */
    protected static function resolveReflection(): ModelReflection
    {
        return new ModelReflection(static::class);
    }

    /**
     * @return Connection
     */
    public function getConnection(): Connection
    {
        $db = static::getManager()->getDatabaseManager();
        return $db->using(static::getReflection()->connectionName);
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return static::getReflection()->tableName;
    }

    /**
     * @param string $name
     * @return Cast
     */
    public function getCast(string $name): Cast
    {
        return static::getReflection()->properties[$name]->cast;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyName(): string
    {
        return static::getReflection()->primaryKey;
    }

    /**
     * @return string|int|null
     */
    public function getPrimaryKey(): string|int|null
    {
        return $this->getProperty($this->getPrimaryKeyName());
    }
}
