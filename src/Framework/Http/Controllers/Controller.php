<?php

namespace App\Framework\Http\Controllers;

use Kirameki\Http\HttpRequest;
use Kirameki\Http\HttpResponse;

abstract class Controller
{
    public abstract function handle(HttpRequest $request): HttpResponse;
}
