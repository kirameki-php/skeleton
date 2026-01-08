<?php declare(strict_types=1);

namespace Kirameki\Framework\Console;

use Kirameki\Framework\Console\Definitions\ArgumentDefinition;
use Kirameki\Framework\Console\Definitions\OptionDefinition;
use function array_keys;

readonly class CommandDefinition
{
    /**
     * @var list<string>
     */
    protected array $argumentIndexAliases;

    /**
     * @param class-string<Command> $class
     * @param string $name
     * @param array<string, ArgumentDefinition> $arguments
     * @param array<string, OptionDefinition> $options
     * @param array<string, string> $shortNameAliases
     * @param string|null $memoryLimit
     * @param int|null $timeLimit
     */
    public function __construct(
        public string $class,
        public string $name,
        public array $arguments,
        public array $options,
        public array $shortNameAliases,
        public ?string $memoryLimit = null,
        public ?int $timeLimit = null,
    ) {
        $this->argumentIndexAliases = array_keys($arguments);
    }

    /**
     * @param int $index
     * @return ArgumentDefinition|null
     */
    public function getArgumentByIndexOrNull(int $index): ?ArgumentDefinition
    {
        $key = $this->argumentIndexAliases[$index] ?? null;
        return $this->arguments[$key ?? -1] ?? null;
    }

    /**
     * @param string $char
     * @return OptionDefinition|null
     */
    public function getOptionByShortOrNull(string $char): ?OptionDefinition
    {
        $key = $this->shortNameAliases[$char] ?? null;
        return $this->options[$key ?? -1] ?? null;
    }
}
