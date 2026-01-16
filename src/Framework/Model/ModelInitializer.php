<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Kirameki\Framework\Foundation\ServiceInitializer;
use Kirameki\Framework\Model\Casts\BoolCast;
use Kirameki\Framework\Model\Casts\InstantCast;
use Kirameki\Framework\Model\Casts\EnumCast;
use Kirameki\Framework\Model\Casts\FloatCast;
use Kirameki\Framework\Model\Casts\IntCast;
use Kirameki\Framework\Model\Casts\StringCast;

class ModelInitializer extends ServiceInitializer
{
    public function initialize(): void
    {
        $models = $this->container->get(ModelManager::class);
        $models->setCast('bool', static fn() => new BoolCast());
        $models->setCast('int', static fn() => new IntCast());
        $models->setCast('float', static fn() => new FloatCast());
        $models->setCast('string', static fn() => new StringCast());
        $models->setCast('instant', static fn() => new InstantCast());
        $models->setCast('{enum}', static fn(string $name) => new EnumCast($name));
    }
}
