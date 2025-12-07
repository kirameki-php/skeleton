<?php declare(strict_types=1);

namespace App\Framework\Http\Routing;

use App\Framework\Http\Controllers\Controller;
use Closure;
use Kirameki\Container\Container;
use Kirameki\Http\HttpMethod;
use Kirameki\Storage\Path;
use Override;

class ControllerRoute extends Route
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
    protected function resolve(Container $container): Closure
    {
        $controller = $container->make($this->controller);
        return $controller->handle(...);
    }
}
