<?php declare(strict_types=1);

namespace App;

use App\Framework\App;
use App\Framework\AppEnv;
use App\Framework\Deployment;
use App\Framework\Logging\LogInitializer;
use Kirameki\Container\Container;
use Kirameki\Storage\Path;
use Kirameki\System\Env;
use function dirname;
use function chdir;
use function dump;
use function microtime;

final readonly class Program
{
    /**
     * @var App
     */
    protected App $app;

    public function __construct()
    {
        $path = Path::of(dirname(__DIR__));

        // Set working directory to base path
        chdir($path->toString());

        $this->app = new App(
            $this->makeAppEnv($path),
            $this->makeDeployment(),
            new Container(),
            microtime(true),
            [LogInitializer::class],
        );
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        $this->app->boot();
    }

    /**
     * @param array<string, mixed> $server
     */
    public function handle(array $server): void
    {
        $this->app->startScope();
    }

    /**
     * @param bool $keepRunning
     * @return void
     */
    public function afterHandled(bool $keepRunning): void
    {
        $this->app->endScope();
    }

    /**
     * @return void
     */
    public function shutdown(): void
    {
        $this->app->terminate();
    }

    /**
     * @param Path $path
     * @return AppEnv
     */
    protected function makeAppEnv(Path $path): AppEnv
    {
        $isDevelopment = Env::getBoolOrNull('APP_DEVELOP_MODE') ?? false;
        $inTestMode = Env::getBoolOrNull('APP_TEST_MODE') ?? false;

        return new AppEnv($path, $isDevelopment, $inTestMode);
    }

    /**
     * @return Deployment
     */
    protected function makeDeployment(): Deployment
    {
        $namespace = Env::getString('APP_NAMESPACE');

        return new Deployment($namespace);
    }
}
