<?php declare(strict_types=1);

namespace Kirameki\Framework\Http\Routing;

use Kirameki\Exceptions\InvalidArgumentException;
use Kirameki\Http\HttpRequest;
use function array_shift;
use function compact;
use function count;
use function explode;
use function preg_match;
use function str_ends_with;
use function str_starts_with;
use function substr;
use function trim;
use const PHP_INT_MAX;

final class HttpRouteTree
{
    /**
     * @var array<string, HttpRouteTree|HttpResource>
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
     * @param int $level
     */
    public function __construct(
        protected int $level = 0,
    ) {
    }

    /**
     * @param HttpRequest $request
     * @return HttpResource|null
     */
    public function find(HttpRequest $request): ?HttpResource
    {
        $path = trim($request->url->path, '/');
        $pathParts = explode('/', $path);
        return $this->findRecursive($pathParts);
    }

    /**
     * @param list<string> $pathParts
     * @return HttpResource|null
     */
    protected function findRecursive(array $pathParts): ?HttpResource
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
     * @param list<HttpRoute>|self|null $node
     * @return HttpResource|null
     */
    protected function resolveNode(array $remainingParts, mixed $node): ?HttpResource
    {
        if ($node instanceof HttpResource) {
            return $node;
        }

        if ($node instanceof self) {
            $result = $node->findRecursive($remainingParts);
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
            $this->nodes[$segment] ??= new HttpResource();
            $this->nodes[$segment]->add($route);
        }
    }
}
