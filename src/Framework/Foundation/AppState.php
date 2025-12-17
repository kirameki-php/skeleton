<?php declare(strict_types=1);

namespace Kirameki\Framework\Foundation;

enum AppState: string
{
    case Constructed = 'constructed';
    case Booting = 'booting';
    case Running = 'running';
    case Terminating = 'terminating';
    case Terminated = 'terminated';
}
