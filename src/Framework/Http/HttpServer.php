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
use function sprintf;

class HttpServer
{
    /**
     * @param EventDispatcher $events
     * @param HttpRouter $router
     */
    public function __construct(
        protected EventDispatcher $events,
        protected HttpRouter $router,
    ) {
    }

    /**
     * @param AppScope $scope
     * @param array<string, mixed> $server
     * @return void
     */
    function run(AppScope $scope, array $server): void
    {
        $request = $this->buildRequestFromEnvs($server);
        $this->events->emit(new RequestReceived($request));
        $then = hrtime(true);
        $response = $this->router->dispatch($scope, $request);
        $this->sendResponse($response);
        $elapsedSeconds = (hrtime(true) - $then) / 1_000_000_000;
        $this->events->emit(new ResponseSent($request, $response, $elapsedSeconds));
    }

    /**
     * @param array<string, mixed> $server
     * @return HttpRequest
     */
    protected function buildRequestFromEnvs(array $server): HttpRequest
    {
        $method = HttpMethod::from($server['REQUEST_METHOD']);
        $version = (float) str_replace('HTTP/', '', $server['SERVER_PROTOCOL'] ?? 'HTTP/1.1');
        $urlString = ($server['HTTP_X_FORWARDED_PROTO'] ?? $server['REQUEST_SCHEME'] ?? 'http') . '://' . $server['HTTP_HOST'] . $server['REQUEST_URI'];
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
