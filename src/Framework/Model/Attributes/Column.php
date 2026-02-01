<?php declare(strict_types=1);

namespace Kirameki\Framework\Model\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Column
{
    public function __construct(public string $name) {}
}
