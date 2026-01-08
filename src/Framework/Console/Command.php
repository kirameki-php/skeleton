<?php declare(strict_types=1);

namespace Kirameki\Framework\Console;

use Closure;
use Kirameki\Collections\Map;
use Kirameki\Framework\Console\Exceptions\CodeOutOfRangeException;
use Kirameki\Framework\Console\Parameters\Argument;
use Kirameki\Framework\Console\Parameters\Option;
use Kirameki\Process\ExitCode;
use Kirameki\Process\Signal;
use Kirameki\Process\SignalEvent;
use function ini_set;
use function set_time_limit;

abstract class Command
{
    /**
     * @var CommandDefinition
     */
    protected CommandDefinition $definition;

    /**
     * @var Map<string, Argument>
     */
    protected Map $arguments;

    /**
     * @var Map<string, Option>
     */
    protected Map $options;

    /**
     * @var ConsoleOutput
     */
    protected ConsoleOutput $output;

    /**
     * @var ConsoleInput
     */
    protected ConsoleInput $input;

    /**
     * @return CommandDefinition
     */
    public static function getDefinition(): CommandDefinition
    {
        $builder = new CommandBuilder(static::class);
        static::define($builder);
        return $builder->build();
    }

    /**
     * Define the command and its arguments and options.
     *
     * @param CommandBuilder $builder
     * @return void
     */
    public static function define(CommandBuilder $builder): void
    {
        // to be implemented by subclasses
    }

    /**
     * Parse the raw parameters and run the command.
     *
     *  Exit code for the given command.
     *  Must be between 0 and 255.
     *
     * @param CommandDefinition $definition
     * @param Map<string, Argument> $arguments
     * @param Map<string, Option> $options
     * @param ConsoleOutput $output
     * @param ConsoleInput $input
     * @return int
     */
    public function execute(
        CommandDefinition $definition,
        Map $arguments,
        Map $options,
        ConsoleOutput $output = new ConsoleOutput(),
        ConsoleInput $input = new ConsoleInput(),
    ): int
    {
        $this->definition = $definition;
        $this->arguments = $arguments;
        $this->options = $options;
        $this->output = $output;
        $this->input = $input;

        $this->applyRuntimeLimits();

        $code = $this->run() ?? ExitCode::SUCCESS;

        if ($code < 0 || $code > 255) {
            throw new CodeOutOfRangeException("Exit code must be between 0 and 255, {$code} given.", [
                'code' => $code,
                'command' => $this,
                'definition' => $this->definition,
                'arguments' => $this->arguments,
                'options' => $this->options,
            ]);
        }

        return $code;
    }

    /**
     * The method which runs the user defined logic.
     *
     * @return int|null
     */
    abstract protected function run(): ?int;

    /**
     * @param int $signal
     * @param Closure(SignalEvent): mixed $callback
     * @return void
     */
    protected function onSignal(int $signal, Closure $callback): void
    {
        Signal::handle($signal, $callback);
    }

    /**
     * @return bool
     */
    protected function isVerbose(): bool
    {
        return $this->options->get('verbose')->provided;
    }

    /**
     * @return void
     */
    private function applyRuntimeLimits(): void
    {
        $this->applyTimeLimit();
        $this->applyMemoryLimit();
    }

    /**
     * @return void
     */
    private function applyTimeLimit(): void
    {
        $option = $this->options->getOrNull('time-limit');

        $timeLimit = $option?->provided
            ? $option->valueAsInt()
            : $this->definition->timeLimit;

        if ($timeLimit !== null) {
            set_time_limit($timeLimit);
        }
    }

    /**
     * @return void
     */
    private function applyMemoryLimit(): void
    {
        // validate format
        $option = $this->options->getOrNull('memory-limit');

        $memoryLimit = $option?->provided
            ? $option->value()
            : $this->definition->memoryLimit;

        if ($memoryLimit !== null) {
            ini_set('memory_limit', $memoryLimit);
        }
    }
}
