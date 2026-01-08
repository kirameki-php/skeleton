<?php declare(strict_types=1);

namespace Kirameki\Framework\Http\Routing;

use Closure;
use Kirameki\Framework\Http\HttpContext;
use Kirameki\Http\HttpMethod;
use Kirameki\Http\HttpRequest;
use Kirameki\Http\HttpResponse;
use Override;

class CallbackHttpRoute extends HttpRoute
{
    /**
     * @param HttpMethod $method
     * @param string $path
     * @param Closure(HttpContext): HttpResponse $handler
     */
    public function __construct(
        HttpMethod $method,
        string $path,
        public readonly Closure $handler,
    ) {
        parent::__construct($method, $path);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function resolve(HttpContext $context): Closure
    {
        return $this->handler;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function filters(HttpContext $context): array
    {
        return [];
    }
}
