<?php declare(strict_types=1);

namespace App\Framework\Http\Routing;

use App\Framework\Http\HttpContext;
use Closure;
use Kirameki\Http\HttpMethod;
use Kirameki\Http\HttpRequest;
use Kirameki\Http\HttpResponse;
use Kirameki\Storage\Path;
use Override;

class CallbackHttpRoute extends HttpRoute
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
    protected function resolve(HttpContext $context): Closure
    {
        return $this->handler;
    }
}
