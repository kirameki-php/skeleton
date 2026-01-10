<?php declare(strict_types=1);

use Kirameki\App\Initializers\ExceptionInitializer;
use Kirameki\App\Initializers\LoggerInitializer;
use Kirameki\App\Initializers\RoutingInitializer;
use Kirameki\Framework\AppBuilder;
use Kirameki\Storage\Path;

$builder = new AppBuilder(Path::of(dirname(__DIR__)));

return $builder
    ->addInitializer(ExceptionInitializer::class)
    ->addInitializer(LoggerInitializer::class)
    ->addInitializer(RoutingInitializer::class);
