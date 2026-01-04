<?php declare(strict_types=1);

namespace Kirameki\Framework\Cli;

use Kirameki\Container\Container;
use Kirameki\Event\EventEmitter;
use Kirameki\Framework\Cli\Events\CommandExecuted;
use Kirameki\Framework\Cli\Events\CommandExecuting;
use Kirameki\Framework\Cli\Exceptions\CliException;
use Kirameki\Framework\Cli\Exceptions\InvalidInputException;
use Kirameki\Framework\Cli\Parameters\ParameterParser;
use Kirameki\Collections\Map;
use Kirameki\Collections\Utils\Arr;
use function array_shift;
use function array_slice;
use function assert;
use function preg_split;
use const PREG_SPLIT_DELIM_CAPTURE;
use const PREG_SPLIT_NO_EMPTY;

class CommandRunner
{
    /**
     * @param Container $container
     * @param CommandRegistry $registry
     * @param EventEmitter $events
     * @param Output $output
     * @param Input $input
     */
    public function __construct(
        protected readonly Container $container,
        protected readonly CommandRegistry $registry,
        protected readonly EventEmitter $events,
        protected readonly Output $output = new Output(),
        protected readonly Input $input = new Input(),
    ) {
    }

    /**
     * @param string $input
     * @return int
     */
    public function parseAndRun(string $input): int
    {
        // Splits $input into command name + parameters.
        // Double-quoted strings are properly handled through the regex below.
        $args = preg_split('/"([^"]*)"|\h+/', $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        assert($args !== false);

        $name = array_shift($args);
        if ($name === null) {
            throw new InvalidInputException('No command name given.', [
                'input' => $input,
            ]);
        }

        return $this->run($name, $args);
    }

    /**
     * @param list<string> $args
     * @return int
     */
    public function runFromArgs(array $args): int
    {
        $name = $args[1] ?? '';
        $parameters = array_slice($args, 2);

        return $this->run($name, $parameters);
    }

    /**
     * @param string|class-string<Command> $name
     * @param iterable<int, string> $parameters
     * @return int
     */
    public function run(string $name, iterable $parameters = []): int
    {
        $events = $this->events;

        try {
            $definition = $this->registry->getDefinition($name);
            $command = $this->container->make($definition->class);
            $parsed = ParameterParser::parse($definition, Arr::values($parameters));

            $events->emit(new CommandExecuting($command));

            $exitCode = $command->execute(
                $definition,
                new Map($parsed['arguments']),
                new Map($parsed['options']),
                $this->output,
                $this->input,
            );

            $events->emit(new CommandExecuted($command, $exitCode));

            return $exitCode;
        }
        catch (CliException $e) {
            return $e->getExitCode();
        }
    }
}
