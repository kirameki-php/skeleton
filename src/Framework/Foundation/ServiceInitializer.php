<?php declare(strict_types=1);

namespace App\Framework\Foundation;

use Kirameki\Container\Container;

interface ServiceInitializer
{
    function register(Container $container): void;
}
