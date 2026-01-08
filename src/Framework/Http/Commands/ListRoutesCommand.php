<?php declare(strict_types=1);

namespace Kirameki\Framework\Http\Commands;

use Kirameki\Framework\Console\Command;
use Kirameki\Framework\Console\CommandBuilder;
use Kirameki\Framework\Http\Routing\HttpRouter;
use Kirameki\Framework\Http\Routing\HttpRouteTree;
use Kirameki\Process\ExitCode;
use Override;
use function sprintf;

class ListRoutesCommand extends Command
{
    /**
     * @inheritDoc
     */
    #[Override]
    public static function define(CommandBuilder $builder): void
    {
        $builder->name('routes');
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
     * @param HttpRouteTree $node
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
