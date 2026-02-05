<?php declare(strict_types=1);

namespace Kirameki\App\Initializers;

use Kirameki\Container\ContainerBuilder;
use Kirameki\Database\Config\DatabaseConfig;
use Kirameki\Database\Config\SqliteConfig;
use Kirameki\Framework\Foundation\AppEnv;
use Kirameki\Framework\Foundation\ServiceInitializer;

class DatabaseInitializer extends ServiceInitializer
{
    public function initialize(): void
    {
        $this->container->singleton(DatabaseConfig::class, function () {
            return new DatabaseConfig(
                [
                    'default' => new SqliteConfig(
                        "{$this->app->path}/storage/database.sqlite"
                    ),
                ],
            );
        });
    }
}
