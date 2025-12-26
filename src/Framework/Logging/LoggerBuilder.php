<?php declare(strict_types=1);

namespace Kirameki\Framework\Logging;

use Kirameki\Exceptions\InvalidArgumentException;
use Kirameki\Framework\Logging\Writers\LogWriter;

class LoggerBuilder
{
    /**
     * @param array<string, LogWriter> $writers
     */
    public function __construct(
        protected array $writers = [],
    ) {
    }

    /**
     * @param string $name
     * @param LogWriter $writer
     * @return $this
     */
    public function addWriter(string $name, LogWriter $writer): static
    {
        if (isset($this->writers[$name])) {
            throw new InvalidArgumentException("LogWriter with name '{$name}' already exists.");
        }

        $this->writers[$name] = $writer;
        return $this;
    }

    /**
     * @return Logger
     */
    public function build(): Logger
    {
        return new Logger($this->writers);
    }
}
