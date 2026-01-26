<?php declare(strict_types=1);

namespace Kirameki\App\Models;

use Kirameki\Framework\Model\Model;

class User extends Model
{
    public int $id {
        get => $this->id ??= $this->getProperty('id');
        set => $this->setProperty('id', $value);
    }
}
