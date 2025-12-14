<?php declare(strict_types=1);

namespace App\Framework\Http;

use Closure;
use Kirameki\Http\HttpRequest;
use Kirameki\Http\HttpResponse;

interface ActionFilter
{
    function handle(HttpRequest $request, Closure $next): HttpResponse;
}
