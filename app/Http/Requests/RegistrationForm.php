<?php declare(strict_types=1);

namespace App\Http\Requests;

use Kirameki\Http\Request\Input;

class RegistrationForm
{
    #[Input(required: true)]
    public string $name;
}
