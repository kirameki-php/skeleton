<?php declare(strict_types=1);

use Kirameki\Framework\AppBuilder;
use Kirameki\Framework\Exception\ExceptionHandlerBuilder;
use Kirameki\Framework\Exception\Reporters\LogReporter;
use Kirameki\Framework\Logging\LoggerBuilder;
use Kirameki\Framework\Logging\LogLevel;
use Kirameki\Framework\Logging\Writers\ConsoleWriter;
use Kirameki\Storage\Path;

$builder = new AppBuilder(Path::of(dirname(__DIR__)));

$builder->configure(static function (LoggerBuilder $logger) {
    $logger->addWriter('console', new ConsoleWriter(LogLevel::Info));
});

$builder->configure(static function (ExceptionHandlerBuilder $handler) {
    $handler
        ->addReporter(LogReporter::class)
        ->addDeprecatedReporter(LogReporter::class)
        ->setFallbackReporter(LogReporter::class);
});

return $builder;
