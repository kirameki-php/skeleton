<?php declare(strict_types=1);

namespace App\Models;

use Kirameki\Model\Model;
use Kirameki\Model\ReflectionBuilder;

/**
 * @property string $id
 * @property string $token
 */
class User extends Model
{
    public static function define(ReflectionBuilder $reflection): void
    {
        $reflection->property('id', 'int');
        $reflection->property('token', 'string');
        $reflection->property('createdAt', 'datetime');
        $reflection->property('updatedAt', 'datetime');
    }
}
