<?php declare(strict_types=1);

namespace App\Framework;

use Kirameki\Container\Container;

interface Initializer
{
    function register(Container $container): void;
}
