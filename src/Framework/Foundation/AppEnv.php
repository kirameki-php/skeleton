<?php declare(strict_types=1);

namespace Kirameki\Framework\Foundation;

use Kirameki\Storage\Path;

class AppEnv
{
    /**
     * @param Path $path
     * @param string $namespace
     * @param bool $isDevelopment
     * @param bool $inTestMode
     */
    public function __construct(
        public readonly Path $path,
        public readonly string $namespace = 'undefined',
        public readonly bool $isDevelopment = false,
        public readonly bool $inTestMode = false,
    ) {
    }
}
