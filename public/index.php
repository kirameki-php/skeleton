<?php

/** @var Kirameki\Core\Application $app */
$app = require '../app/boot.php';

$user = new \App\Models\User();
$user->id = uuid();
$user->token = uuid();
$user->save();

$user = \App\Models\User::query()->orderByDesc('createdAt')->first();

dump($user);
