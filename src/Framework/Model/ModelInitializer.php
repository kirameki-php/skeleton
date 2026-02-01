<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Kirameki\Container\ContainerBuilder;
use Kirameki\Framework\Foundation\AppEnv;
use Kirameki\Framework\Foundation\ServiceInitializer;
use Kirameki\Framework\Model\Casts\BoolCast;
use Kirameki\Framework\Model\Casts\InstantCast;
use Kirameki\Framework\Model\Casts\EnumCast;
use Kirameki\Framework\Model\Casts\FloatCast;
use Kirameki\Framework\Model\Casts\IntCast;
use Kirameki\Framework\Model\Casts\StringCast;
use Kirameki\Time\Instant;

class ModelInitializer extends ServiceInitializer
{
    public static function register(ContainerBuilder $container, AppEnv $env): void
    {
        $container->singleton(ModelManager::class);
    }

    public function initialize(): void
    {
        $models = $this->container->get(ModelManager::class);
        $models->setCast('bool', static fn() => new BoolCast());
        $models->setCast('int', static fn() => new IntCast());
        $models->setCast('float', static fn() => new FloatCast());
        $models->setCast('string', static fn() => new StringCast());
        $models->setCast(Instant::class, static fn() => new InstantCast());
    }
}
