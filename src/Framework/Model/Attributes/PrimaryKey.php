<?php declare(strict_types=1);

namespace Kirameki\Framework\Model\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class PrimaryKey
{
    public function __construct(public ?int $position = null) {}
}
