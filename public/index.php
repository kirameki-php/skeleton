<?php declare(strict_types=1);

use Kirameki\Http\HttpHandler;
use Kirameki\Http\Request;

/** @var Kirameki\Core\Application $app */
$app = require '../app/boot.php';

/** @var HttpHandler $http */
$http = $app->get(HttpHandler::class);

$response = $http->process(Request::fromServerVars());

dump($response->toString());