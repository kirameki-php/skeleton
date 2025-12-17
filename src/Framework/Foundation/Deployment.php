<?php declare(strict_types=1);

namespace Kirameki\Framework\Foundation;

class Deployment
{
    /**
     * @param string $namespace
     * @param float $deployedTimeFloat
     * @param string $deployer
     * @param string $revision
     * @param string $title
     */
    public function __construct(
        public readonly string $namespace,
        public readonly float $deployedTimeFloat = 0.0,
        public readonly string $deployer = '',
        public readonly string $revision = '',
        public readonly string $title = '',
    ) {
    }
}
