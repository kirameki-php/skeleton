<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Kirameki\Framework\Foundation\ServiceInitializer;
use Kirameki\Framework\Model\Casts\BoolCast;
use Kirameki\Framework\Model\Casts\InstantCast;
use Kirameki\Framework\Model\Casts\FloatCast;
use Kirameki\Framework\Model\Casts\IntCast;
use Kirameki\Framework\Model\Casts\StringCast;
use Kirameki\Time\Instant;

class ModelInitializer extends ServiceInitializer
{
    public function initialize(): void
    {
        $entry = $this->container->builder(TypeCasterCollectionBuilder::class, TypeCasterCollection::class);
        $entry->configure(static function (TypeCasterCollectionBuilder $casters) {
            $casters->set('bool', static fn() => new BoolCast());
            $casters->set('int', static fn() => new IntCast());
            $casters->set('float', static fn() => new FloatCast());
            $casters->set('string', static fn() => new StringCast());
            $casters->set(Instant::class, static fn() => new InstantCast());
        });
    }
}
