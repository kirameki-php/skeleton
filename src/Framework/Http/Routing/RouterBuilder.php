<?php declare(strict_types=1);

namespace App\Framework\Http\Routing;

use App\Framework\Http\Controllers\Controller;
use Closure;
use Kirameki\Container\Container;
use Kirameki\Http\HttpMethod;
use Kirameki\Http\HttpRequest;
use Kirameki\Http\HttpResponse;
use Kirameki\Storage\Path;

class RouterBuilder
{
    /**
     * @var list<Route>
     */
    protected array $routes = [];

    /**
     * @param Container $container
     */
    public function __construct(
        protected Container $container,
    ) {
    }

    /**
     * @param HttpMethod $method
     * @param Path $path
     * @param class-string<Controller>|Closure(HttpRequest): HttpResponse $controller
     * @return $this
     */
    public function addRoute(HttpMethod $method, Path $path, string|Closure $controller): static
    {
        $this->routes[] = ($controller instanceof Closure)
            ? new CallbackRoute($method, $path, $controller)
            : new ControllerRoute($method, $path, $controller);
        return $this;
    }

    /**
     * @return Router
     */
    public function build(): Router
    {
        return new Router($this->container, $this->routes);
    }
}
