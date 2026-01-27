<?php declare(strict_types=1);

namespace Kirameki\Framework\Model\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Table
{
    public function __construct(
        public ?string $name = null,
        public ?string $connection = null,
    ) {}
}
