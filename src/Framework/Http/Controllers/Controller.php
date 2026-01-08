<?php declare(strict_types=1);

namespace Kirameki\Framework\Http\Controllers;

use Kirameki\Framework\Http\Routing\HttpRoute;
use Kirameki\Container\Container;
use Kirameki\Http\HttpRequest;
use Kirameki\Http\HttpResponse;
use Kirameki\Http\HttpResponseBody;
use Kirameki\Http\HttpResponseHeaders;

abstract class Controller
{
    /**
     * @param Container $container
     * @param HttpRequest $request
     * @param HttpRoute $route
     */
    public function __construct(
        public readonly Container $container,
        public readonly HttpRequest $request,
        public readonly HttpRoute $route,
    ) {
    }

    /**
     * @return HttpResponse
     */
    public abstract function handle(): HttpResponse;

    /**
     * @param int $statusCode
     * @return HttpResponse
     */
    public function response(int $statusCode = 200): HttpResponse
    {
        return new HttpResponse(
            $this->request->version,
            $statusCode,
            new HttpResponseHeaders(),
            new HttpResponseBody(),
        );
    }
}
