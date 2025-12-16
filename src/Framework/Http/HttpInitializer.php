<?php declare(strict_types=1);

namespace App\Framework\Http;

use App\Framework\Foundation\ServiceInitializer;
use Kirameki\Container\Container;

class HttpInitializer implements ServiceInitializer
{
    function register(Container $container): void
    {
        $container->singleton(HttpRunner::class, static fn(Container $c) => $c->make(HttpRunner::class));
    }
}
