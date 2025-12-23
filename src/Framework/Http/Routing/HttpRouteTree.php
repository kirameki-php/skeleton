<?php declare(strict_types=1);

namespace Kirameki\Framework\Http\Routing;

use Kirameki\Exceptions\InvalidArgumentException;
use Kirameki\Http\HttpRequest;
use function compact;
use function count;
use function dump;
use function explode;
use function is_string;
use function preg_match;
use function str_starts_with;
use function substr;
use const PHP_INT_MAX;

final class HttpRouteTree
{
    /**
     * @var array<string, HttpRoute|HttpRouteTree>
     */
    protected array $nodes = [];

    /**
     * @var array<string, int>
     */
    protected array $exactIndexMap = [];

    /**
     * @var array<string, array{index: int, regex: string}>
     */
    protected array $regexIndexMap = [];

    /**
     * @param list<HttpRoute> $routes
     * @return static
     */
    public static function for(array $routes = []): self
    {
        $self = new self();
        foreach ($routes as $route) {
            $segments = explode('/', trim($route->path, '/'));
            $self->add($segments, $route);
        }
        return $self;
    }

    /**
     * @param int $level
     */
    protected function __construct(
        protected int $level = 0,
    ) {
    }

    /**
     * @param HttpRequest $request
     * @return HttpRoute|null
     */
    public function find(HttpRequest $request): ?HttpRoute
    {
        $path = trim($request->url->path, '/');
        return $this->findRecursive(explode('/', $path));
    }

    /**
     * @param list<string> $pathParts
     * @return HttpRoute|null
     */
    protected function findRecursive(array $pathParts): ?HttpRoute
    {
        $part = array_shift($pathParts);

        if (count($this->regexIndexMap) === 0) {
            return $this->resolveNode($pathParts, $this->nodes[$part] ?? null);
        }

        $exactIndex = $this->exactIndexMap[$part] ?? PHP_INT_MAX;

        foreach ($this->regexIndexMap as $segment => $data) {
            // if exact match has higher priority, simply
            if ($exactIndex < $data['index']) {
                $route = $this->resolveNode($pathParts, $this->nodes[$part]);
                if ($route !== null) {
                    return $route;
                }
            }

            // now check for param match
            if (preg_match($data['regex'], $part)) {
                $route = $this->resolveNode($pathParts, $this->nodes[$segment]);
                if ($route !== null) {
                    return $route;
                }
            }
        }
        return null;
    }

    /**
     * @param list<string> $remainingParts
     * @param object|null $route
     * @return HttpRoute|null
     */
    protected function resolveNode(array $remainingParts, ?object $route): ?HttpRoute
    {
        if ($route instanceof HttpRoute) {
            return $route;
        }
        if ($route instanceof self) {
            $result = $route->findRecursive($remainingParts);
            if ($result !== null) {
                return $result;
            }
        }
        return null;
    }

    /**
     * @param list<string> $segments
     * @param HttpRoute $route
     * @return void
     */
    public function add(array $segments, HttpRoute $route): void
    {
        $segment = array_shift($segments);
        $index = count($this->nodes);

        if (str_starts_with($segment, '{') && str_ends_with($segment, '}')) {
            $type = explode('|', substr($segment, 1, -1), 2)[1] ?? null;
            $regex = match ($type) {
                'int' => '~^\d+$~',
                null => '~^[a-zA-Z0-9-_]+$~',
                default => throw new InvalidArgumentException("Unsupported route parameter type: {$type}"),
            };
            $this->regexIndexMap[$segment] = compact('index', 'regex');
        } else {
            $this->exactIndexMap[$segment] = $index;
        }

        if (count($segments) !== 0) {
            $this->nodes[$segment] ??= new HttpRouteTree($this->level + 1);
            $this->nodes[$segment]->add($segments, $route);
        } else {
            $this->nodes[$segment] = $route;
        }
    }
}
