<?php declare(strict_types=1);

use Kirameki\Http\HttpManager;
use Kirameki\Http\Request;

/** @var Kirameki\Core\Application $app */
$app = require '../app/boot.php';

/** @var HttpManager $http */
$http = $app->get(HttpManager::class);

$response = $http->process(Request::fromServerVars());

dump($response->toString());

$http->send($response);