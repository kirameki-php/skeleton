<?php declare(strict_types=1);

namespace App\Framework\Http;

use App\Framework\Foundation\AppRunner;
use App\Framework\Http\Events\RequestReceived;
use App\Framework\Http\Events\ResponseSent;
use App\Framework\Http\Routing\HttpRouter;
use Kirameki\Event\EventDispatcher;
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

class HttpRunner implements AppRunner
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
     * @param array<string, mixed> $server
     * @return void
     */
    function run(array $server): void
    {
        $request = $this->buildRequestFromEnvs();
        $this->events->emit(new RequestReceived($request));
        $then = hrtime(true);
        $response = $this->router->dispatch($request);
        $this->sendResponse($response);
        $elapsedSeconds = (hrtime(true) - $then) / 1_000_000_000;
        $this->events->emit(new ResponseSent($request, $response, $elapsedSeconds));
    }

    /**
     * @return void
     */
    function terminate(): void
    {
    }

    /**
     * @return HttpRequest
     */
    protected function buildRequestFromEnvs(): HttpRequest
    {
        $method = $_REQUEST['_method'] ?? $_SERVER['REQUEST_METHOD'];
        $version = (float) str_replace('HTTP/', '', $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1');
        $urlString = ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? $_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $url = Url::parse($urlString);
        $headers = new HttpRequestHeaders(getallheaders());
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
