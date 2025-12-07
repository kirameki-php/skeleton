<?php declare(strict_types=1);

namespace App\Framework\Http\Routing;

use Closure;
use Kirameki\Container\Container;
use Kirameki\Http\HttpMethod;
use Kirameki\Http\HttpRequest;
use Kirameki\Http\HttpResponse;
use Kirameki\Storage\Path;
use Override;

class CallbackRoute extends Route
{
    /**
     * @param HttpMethod $method
     * @param Path $path
     * @param Closure(HttpRequest): HttpResponse $handler
     */
    public function __construct(
        HttpMethod $method,
        Path $path,
        public readonly Closure $handler,
    ) {
        parent::__construct($method, $path);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function resolve(Container $container): Closure
    {
        return $this->handler;
    }
}
