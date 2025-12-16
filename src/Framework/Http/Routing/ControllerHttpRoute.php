<?php declare(strict_types=1);

namespace App\Framework\Http\Routing;

use App\Framework\Http\Controllers\Controller;
use App\Framework\Http\HttpContext;
use Closure;
use Kirameki\Http\HttpMethod;
use Kirameki\Storage\Path;
use Override;

class ControllerHttpRoute extends HttpRoute
{
    /**
     * @param HttpMethod $method
     * @param Path $path
     * @param class-string<Controller> $controller
     */
    public function __construct(
        HttpMethod $method,
        Path $path,
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
        $controller = $context->container->make($this->controller);
        return $controller->handle(...);
    }
}
