<?php declare(strict_types=1);

namespace Kirameki\Framework\Http\Initializers;

use Kirameki\Container\Container;
use Kirameki\Framework\Foundation\ServiceInitializer;
use Kirameki\Framework\Http\HttpRunner;

class HttpInitializer extends ServiceInitializer
{
    public function register(Container $container): void
    {
        $container->singleton(HttpRunner::class, static fn(Container $c) => $c->inject(HttpRunner::class));
    }
}
