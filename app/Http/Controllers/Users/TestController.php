<?php declare(strict_types=1);

namespace App\Http\Controllers\Users;

use App\Http\Requests\UserRequestData;
use Kirameki\Http\Controller;
use Kirameki\Http\Routing\Route;

class TestController extends Controller
{
    #[Route('GET', 'users')]
    public function index(UserRequestData $data)
    {
        ddd($data);

        return '';
    }
}
