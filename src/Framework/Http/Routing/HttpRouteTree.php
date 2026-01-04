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
     * @var array<string, HttpRouteTree>
     */
    public array $nodes = [];

    /**
     * @var array<string, int>
     */
    protected array $exactIndexMap = [];

    /**
     * @var array<string, array{index: int, regex: string}>
     */
    protected array $regexIndexMap = [];

    /**
     * @var HttpResource|null
     */
    public HttpResource|null $resource = null;

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

        if ($part === null) {
            return $this->resource;
        }

        if (count($this->regexIndexMap) === 0) {
            return $this->nodes[$part]->findRecursive($pathParts);
        }

        $exactIndex = $this->exactIndexMap[$part] ?? PHP_INT_MAX;

        foreach ($this->regexIndexMap as $segment => $data) {
            // exact match has higher priority
            if ($exactIndex < $data['index']) {
                $route = $this->nodes[$part]->findRecursive($pathParts);
                if ($route !== null) {
                    return $route;
                }
            }

            // now check for param match
            if (preg_match($data['regex'], $part)) {
                $route = $this->nodes[$segment]->findRecursive($pathParts);
                if ($route !== null) {
                    return $route;
                }
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
        if ($segments === []) {
            $this->resource ??= new HttpResource();
            $this->resource->add($route);
            return;
        }

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

        $this->nodes[$segment] ??= new HttpRouteTree($this->level + 1);
        $this->nodes[$segment]->add($segments, $route);
    }
}
