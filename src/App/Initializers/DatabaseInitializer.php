<?php declare(strict_types=1);

namespace Kirameki\App\Initializers;

use Kirameki\Container\ContainerBuilder;
use Kirameki\Database\Config\DatabaseConfig;
use Kirameki\Database\Config\SqliteConfig;
use Kirameki\Framework\Foundation\AppEnv;
use Kirameki\Framework\Foundation\ServiceInitializer;

class DatabaseInitializer extends ServiceInitializer
{
    public static function register(ContainerBuilder $container, AppEnv $env): void
    {
        $container->singleton(DatabaseConfig::class, function () use ($env) {
            return new DatabaseConfig(
                [
                    'default' => new SqliteConfig(
                        "{$env->path}/storage/database.sqlite"
                    ),
                ],
            );
        });
    }
}
