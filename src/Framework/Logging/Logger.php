<?php declare(strict_types=1);

namespace Kirameki\Framework\Logging;

use Kirameki\Framework\Logging\Writers\LogWriter;
use Override;
use Stringable;
use function microtime;

class Logger
{
    /**
     * @param array<string, LogWriter> $writers
     */
    public function __construct(
        protected array $writers = [],
    ) {
    }

    /**
     * @param LogLevel $level
     * @return bool
     */
    public function isEnabled(LogLevel $level): bool
    {
        return array_any($this->writers, static fn(LogWriter $w) => $w->isEnabled($level));
    }

    /**
     * @param LogLevel $level
     * @return $this
     */
    public function setLevel(LogLevel $level): static
    {
        foreach ($this->writers as $writer) {
            $writer->setLevel($level);
        }
        return $this;
    }

    /**
     * @param LogLevel $level
     * @param Stringable|string $message
     * @param array<string, mixed> $context
     */
    #[Override]
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $time = microtime(true);
        foreach ($this->writers as $writer) {
            $writer->write(new LogRecord($level, (string) $message, $context, $time));
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function debug(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::Debug, $message, $context);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function info(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::Info, $message, $context);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function notice(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::Notice, $message, $context);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function warning(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::Warning, $message, $context);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function error(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::Error, $message, $context);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function critical(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::Critical, $message, $context);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function alert(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::Alert, $message, $context);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function emergency(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::Emergency, $message, $context);
    }
}
