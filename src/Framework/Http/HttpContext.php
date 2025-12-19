<?php declare(strict_types=1);

namespace Kirameki\Framework\Http;

use Kirameki\Framework\Foundation\AppScope;
use Kirameki\Framework\Http\Routing\HttpRoute;
use Kirameki\Container\Container;
use Kirameki\Http\HttpRequest;

class HttpContext
{
    /**
     * @param Container $container
     * @param AppScope $appScope
     * @param HttpRequest $request
     * @param HttpRoute $route
     */
    public function __construct(
        public readonly Container $container,
        public readonly AppScope $appScope,
        public readonly HttpRequest $request,
        public readonly HttpRoute $route,
    ) {
    }
}
