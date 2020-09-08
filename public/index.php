<?php

/** @var Kirameki\Application $app */
$app = require '../app/boot.php';

apcu_store(['testa' => 1, 'testb' => 2], 13242, 100);
$iter = new APCuIterator('//', APC_ITER_KEY | APC_ITER_TTL | APC_ITER_CTIME);
foreach ($iter as $data) {
    dump($data);
}

$res = apcu_fetch(['testa', 'testb', 'testc']);
dump($res);