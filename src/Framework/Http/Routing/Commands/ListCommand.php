<?php declare(strict_types=1);

namespace Kirameki\Framework\Http\Routing\Commands;

use Kirameki\Framework\Cli\Command;
use Kirameki\Framework\Cli\CommandBuilder;
use Kirameki\Framework\Http\Routing\HttpRoute;
use Kirameki\Framework\Http\Routing\HttpRouter;
use Kirameki\Framework\Http\Routing\HttpRouteTree;
use Kirameki\Process\ExitCode;
use Override;
use function sprintf;

class ListCommand extends Command
{
    /**
     * @inheritDoc
     */
    #[Override]
    public static function define(CommandBuilder $builder): void
    {
        $builder->name('route:list');
        $builder->option('path', 'p')
            ->description('Filter by route path')
            ->requiresValue();
    }

    public function __construct(
        protected readonly HttpRouter $router,
    ) {
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function run(): ?int
    {
        $this->processNode($this->router->tree);

        return ExitCode::SUCCESS;
    }

    /**
     * @param list<HttpRoute> $routes
     * @return void
     */
    protected function processNode(HttpRouteTree $node): void
    {
        foreach ($node->resource->routes ?? [] as $method => $route) {
            $this->output->line(sprintf('%-6s %s', $method, $route->path));
        }

        foreach ($node->nodes as $child) {
            $this->processNode($child);
        }
    }
}
