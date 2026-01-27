<?php declare(strict_types=1);

namespace Kirameki\Framework\Model\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class PrimaryKey
{
    /**
     * @param list<string> $names
     */
    public function __construct(public array $names) {}
}
