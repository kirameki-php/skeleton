<?php declare(strict_types=1);

namespace Kirameki\Framework\Http\Routing;

use Closure;
use Kirameki\Framework\Http\Controllers\Controller;
use Kirameki\Framework\Http\HttpContext;
use Kirameki\Http\HttpMethod;
use Override;

class ControllerHttpRoute extends HttpRoute
{
    /**
     * @param HttpMethod $method
     * @param string $path
     * @param class-string<Controller> $controller
     */
    public function __construct(
        HttpMethod $method,
        string $path,
        public readonly string $controller,
    ) {
        parent::__construct($method, $path);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function resolve(HttpContext $context): Closure
    {
        $controller = $context->container->make($this->controller, [
            'request' => $context->request,
            'route' => $context->route,
        ]);
        return $controller->handle(...);
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
