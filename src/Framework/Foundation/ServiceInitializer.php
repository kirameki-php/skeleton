<?php declare(strict_types=1);

namespace Kirameki\Framework\Foundation;

use Kirameki\Container\Container;

interface ServiceInitializer
{
    function register(Container $container): void;
}
