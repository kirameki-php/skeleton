<?php declare(strict_types=1);

namespace Kirameki\Framework\Foundation;

use Closure;
use Kirameki\Container\Builder;
use Kirameki\Container\Container;
use Kirameki\Container\ContainerBuilder;
use Kirameki\Exceptions\LogicException;
use Kirameki\Exceptions\RuntimeException;
use Kirameki\Framework\AppBuilder;
use Kirameki\Framework\Console\CommandRegistry;
use ReflectionFunction;
use ReflectionNamedType;

abstract class ServiceInitializer
{
    /**
     * @param AppBuilder $app
     * @param ContainerBuilder $container
     * @param CommandRegistry $commands
     * @return void
     */
    final public function __construct(
        protected readonly AppBuilder $app,
        protected readonly ContainerBuilder $container,
        protected readonly CommandRegistry $commands,
    ) {
    }

    /**
     * @return void
     */
    public function initialize(): void
    {
        // Override in subclass
    }

    /**
     * @template TBuilder of Builder
     * @param Closure(TBuilder): mixed $callback
     * @return $this
     */
    protected function configure(Closure $callback): static
    {
        $builderClass = $this->getClassFromClosure($callback);
        $this->container->configure($builderClass, $callback);
        return $this;
    }

    /**
     * @template TBuilder of Builder
     * @param Closure(TBuilder): mixed $callback
     * @return class-string<TBuilder>
     */
    protected function getClassFromClosure(Closure $callback): string
    {
        $reflection = new ReflectionFunction($callback);
        $params = $reflection->getParameters();
        if (count($params) === 0) {
            throw new RuntimeException("Closure must accept at least one parameter.");
        }
        $paramType = $params[0]->getType();
        if (!$paramType instanceof ReflectionNamedType) {
            throw new LogicException("Closure's first parameter must be a class type.");
        }
        $name = $paramType->getName();
        if (!is_a($name, Builder::class, true)) {
            throw new LogicException("Closure's first parameter must be a class implementing Builder interface.");
        }
        /** @var class-string<TBuilder> */
        return $name;
    }
}
