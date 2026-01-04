<?php declare(strict_types=1);

namespace Kirameki\Framework\Cli;

use Kirameki\Framework\Cli\Exceptions\CommandNotFoundException;
use Kirameki\Framework\Cli\Exceptions\DuplicateEntryException;
use function array_key_exists;
use function array_keys;
use function class_exists;
use function is_subclass_of;

class CommandRegistry
{
    /**
     * @param array<string, CommandDefinition> $registered
     */
    public function __construct(
        protected array $registered = [],
    ) {
    }

    /**
     * @param class-string<Command> $command
     * @return $this
     */
    public function register(string $command): static
    {
        $definition = $command::getDefinition();

        if (array_key_exists($definition->name, $this->registered)) {
            throw new DuplicateEntryException($command, [
                'command' => $command,
            ]);
        }

        $this->registered[$definition->name] = $definition;

        return $this;
    }

    /**
     * @internal for CommandRunner use only
     * @param string|class-string<Command> $name
     * @return CommandDefinition
     */
    public function getDefinition(string $name): CommandDefinition
    {
        if (is_subclass_of($name, Command::class)) {
            return $name::getDefinition();
        }

        if (isset($this->registered[$name])) {
            return $this->registered[$name];
        }

        throw new CommandNotFoundException($name, [
            'name' => $name,
            'registeredCommands' => array_keys($this->registered),
        ]);
    }
}
