<?php declare(strict_types=1);

namespace App\Framework\Http\Routing;

use Closure;
use Kirameki\Container\Container;
use Kirameki\Http\HttpMethod;
use Kirameki\Http\HttpRequest;
use Kirameki\Http\HttpResponse;
use Kirameki\Storage\Path;

abstract class Route
{
    /**
     * @param HttpMethod $method
     * @param Path $path
     */
    public function __construct(
        public readonly HttpMethod $method,
        public readonly Path $path,
    ) {
    }

    /**
     * @param Container $container
     * @param HttpRequest $request
     * @return HttpResponse
     */
    public function run(Container $container, HttpRequest $request): HttpResponse
    {
        $container->scoped(self::class, fn() => $this);
        return ($this->resolve($container))($request);
    }

    /**
     * @param Container $container
     * @return Closure(HttpRequest): HttpResponse
     */
    abstract protected function resolve(Container $container): Closure;
}
