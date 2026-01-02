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
     * @param string|null $action
     */
    public function __construct(
        HttpMethod $method,
        string $path,
        public readonly string $controller,
        public readonly ?string $action = null,
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
        return $controller->{$this->action ?? 'handle'}(...);
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
