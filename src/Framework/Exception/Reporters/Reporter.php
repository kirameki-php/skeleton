<?php declare(strict_types=1);

namespace Kirameki\Framework\Exception\Reporters;

use Throwable;

interface Reporter
{
    /**
     * @param Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void;
}
