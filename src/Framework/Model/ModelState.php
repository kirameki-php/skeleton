<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

enum ModelState: string
{
    case New = 'new';
    case Stored = 'stored';
    case Dirty = 'dirty';
    case Processing = 'processing';
    case Deleted = 'deleted';
}
