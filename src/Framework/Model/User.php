<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

class User extends Model
{
    public int $id {
        get => $this->id;
        set => $this->set('id', $value);
    }
}
