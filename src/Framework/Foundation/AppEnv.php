<?php declare(strict_types=1);

namespace Kirameki\Framework\Foundation;

use Kirameki\Storage\Path;

class AppEnv
{
    /**
     * @var bool
     */
    public bool $isProduction {
        get => !$this->isDevelopment;
    }

    /**
     * @param Path $basePath
     * @param bool $isDevelopment
     * @param bool $inTestMode
     */
    public function __construct(
        public readonly Path $basePath,
        public readonly bool $isDevelopment = false,
        public readonly bool $inTestMode = false,
    ) {
    }
}
