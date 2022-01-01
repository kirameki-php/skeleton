<?php declare(strict_types=1);

namespace App\Http\Requests;

use Kirameki\Http\Request\Input;
use Kirameki\Support\Collection;

class UserRequestData
{
    #[Input(required: true, arrayOf: RegistrationForm::class)]
    public Collection $registrations;
}
