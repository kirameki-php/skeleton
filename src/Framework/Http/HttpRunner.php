<?php declare(strict_types=1);

namespace App\Framework\Http;

use App\Framework\Foundation\AppRunner;
use App\Framework\Http\Routing\Router;
use Kirameki\Http\HttpBody;
use Kirameki\Http\HttpRequest;
use Kirameki\Http\HttpRequestHeaders;
use Kirameki\Http\HttpResponse;
use Kirameki\Http\StatusCode;
use Kirameki\Http\Url;
use function file_get_contents;
use function getallheaders;
use function sprintf;

class HttpRunner implements AppRunner
{
    /**
     * @param Router $router
     */
    public function __construct(
        protected Router $router,
    ) {
    }

    function run(): void
    {
        $request = self::buildRequestFromEnvs();
        $response = $this->router->dispatch($request);
        $this->sendResponse($response);
    }

    function terminate(): void
    {
    }

    /**
     * @return HttpRequest
     */
    public static function buildRequestFromEnvs(): HttpRequest
    {
        $protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
        $version = (float) str_replace('HTTP/', '', $protocol);
        $method = $_REQUEST['_method'] ?? $_SERVER['REQUEST_METHOD'];
        $urlString = ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? $_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $url = Url::parse($urlString);
        $headers = new HttpRequestHeaders(getallheaders());
        $body = new HttpBody(file_get_contents('php://input'));
        return new HttpRequest($method, $version, $url, $headers, $body);
    }

    public static function sendResponse(HttpResponse $response): void
    {
        // Send status line
        $statusCode = $response->statusCode;
        $statusLine = sprintf('%s %d %s', $response->protocolVersion(), $statusCode, StatusCode::asPhrase($statusCode));
        header($statusLine, true, $statusCode);

        // Send headers
        foreach ($response->headers as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }

        // Send body
        echo $response->body->toString();
    }
}
