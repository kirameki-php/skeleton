<?php

namespace App\Framework\Http\Controllers;

use Kirameki\Http\HttpRequest;
use Kirameki\Http\HttpResponse;

interface Controller
{
    function handle(HttpRequest $request): HttpResponse;
}
