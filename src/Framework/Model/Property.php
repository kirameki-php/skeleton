<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Kirameki\Framework\Model\Casts\Cast;

class Property
{
    /**
     * @var string
     */
    public string $name;

    /**
     * @var Cast
     */
    public Cast $cast;

    /**
     * @param string $name
     * @param Cast $cast
     */
    public function __construct(string $name, Cast $cast)
    {
        $this->name = $name;
        $this->cast = $cast;
    }
}
