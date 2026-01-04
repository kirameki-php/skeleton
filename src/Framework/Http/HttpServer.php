<?php declare(strict_types=1);

namespace Kirameki\Framework\Http;

use Kirameki\Framework\Foundation\AppScope;
use Kirameki\Framework\Http\Events\RequestReceived;
use Kirameki\Framework\Http\Events\ResponseSent;
use Kirameki\Framework\Http\Routing\HttpRouter;
use Kirameki\Event\EventDispatcher;
use Kirameki\Http\HttpMethod;
use Kirameki\Http\HttpRequest;
use Kirameki\Http\HttpRequestBody;
use Kirameki\Http\HttpRequestHeaders;
use Kirameki\Http\HttpResponse;
use Kirameki\Http\StatusCode;
use Kirameki\Http\Url;
use function file_get_contents;
use function getallheaders;
use function hrtime;
use function pcntl_async_signals;
use function pcntl_signal;
use function sprintf;
use function touch;
use function unlink;
use const SIGINT;
use const SIGTERM;

class HttpServer
{
    /**
     * @param EventDispatcher $events
     * @param HttpRouter $router
     * @param list<int> $terminalSignals
     */
    public function __construct(
        protected EventDispatcher $events,
        protected HttpRouter $router,
        array $terminalSignals = [SIGTERM, SIGINT],
    ) {
        $this->captureTerminalSignals($terminalSignals);
        $this->markAsReady();
    }

    /**
     * @param AppScope $scope
     * @param array<string, mixed> $info
     * @return void
     */
    public function run(AppScope $scope, array $info): void
    {
        $request = $this->buildRequestFromEnvs($info);
        $this->events->emit(new RequestReceived($request));
        $then = hrtime(true);
        $response = $this->router->dispatch($scope, $request);
        $this->sendResponse($response);
        $elapsedSeconds = (hrtime(true) - $then) / 1_000_000_000;
        $this->events->emit(new ResponseSent($request, $response, $elapsedSeconds));
    }

    /**
     * @param list<int> $signals
     * @return void
     */
    protected function captureTerminalSignals(array $signals): void
    {
        pcntl_async_signals(true);
        foreach ($signals as $signal) {
            pcntl_signal($signal, $this->markAsTerminating(...));
        }
    }

    /**
     * @return void
     */
    protected function markAsReady(): void
    {
        touch(HealthCheck::READINESS_FILE);
    }

    /**
     * @return void
     */
    protected function markAsTerminating(): void
    {
        @unlink(HealthCheck::READINESS_FILE);
    }

    /**
     * @param array<string, mixed> $info
     * @return HttpRequest
     */
    protected function buildRequestFromEnvs(array $info): HttpRequest
    {
        $method = HttpMethod::from($info['REQUEST_METHOD']);
        $version = (float) str_replace('HTTP/', '', $info['SERVER_PROTOCOL'] ?? 'HTTP/1.1');
        $urlString = ($info['HTTP_X_FORWARDED_PROTO'] ?? $info['REQUEST_SCHEME'] ?? 'http') . '://' . $info['HTTP_HOST'] . $info['REQUEST_URI'];
        $url = Url::parse($urlString);
        $headers = new HttpRequestHeaders();
        foreach (getallheaders() as $name => $value) {
            $headers->add($name, $value);
        }
        $body = new HttpRequestBody(file_get_contents('php://input'));
        return new HttpRequest($method, $version, $url, $headers, $body);
    }

    /**
     * @param HttpResponse $response
     * @return void
     */
    protected function sendResponse(HttpResponse $response): void
    {
        $statusCode = $response->statusCode;
        $statusLine = sprintf('%s %d %s', $response->protocolVersion(), $statusCode, StatusCode::asPhrase($statusCode));
        header($statusLine, true, $statusCode);
        foreach ($response->headers as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }
        echo $response->body->toString();
    }
}
