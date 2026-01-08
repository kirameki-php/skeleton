<?php declare(strict_types=1);

namespace Kirameki\Framework\Foundation;

class Deployment
{
    /**
     * @param string $deployer
     * @param string $revision
     * @param float $deployedTimeFloat
     */
    public function __construct(
        public readonly string $deployer = '',
        public readonly string $revision = '',
        public readonly float $deployedTimeFloat = 0.0,
    ) {
    }
}
