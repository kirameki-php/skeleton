<?php declare(strict_types=1);

namespace Kirameki\Framework\Logging\Writers;

use Kirameki\Exceptions\RuntimeException;
use Kirameki\Framework\Logging\Formatters\Formatter;
use Kirameki\Framework\Logging\LogLevel;
use Kirameki\Framework\Logging\LogRecord;
use Override;
use function chmod;
use function fclose;
use function flock;
use function fopen;
use function fwrite;
use function is_resource;
use const LOCK_EX;
use const LOCK_UN;

class StreamWriter extends LogWriter
{
    /**
     * @var resource|null
     */
    protected $stream = null;

    /**
     * @param string $path
     * @param LogLevel $level
     * @param Formatter $formatter
     * @param int|null $filePermission
     * @param bool $useLocking
     */
    public function __construct(
        protected readonly string $path,
        LogLevel $level,
        protected Formatter $formatter,
        protected ?int $filePermission = null,
        protected bool $useLocking = false,
    ) {
        parent::__construct($level);
    }

    /**
     * @return void
     */
    public function close(): void
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
        $this->stream = null;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function handle(LogRecord $record): void
    {
        $stream = $this->openStreamOnce();

        if ($this->useLocking) {
            flock($stream, LOCK_EX);
        }
        try {
            fwrite($stream, $this->formatter->format($record));
        }
        finally {
            if ($this->useLocking) {
                flock($stream, LOCK_UN);
            }
        }
    }

    /**
     * @return resource
     */
    protected function openStreamOnce(): mixed
    {
        return $this->stream ??= $this->openStream();
    }

    /**
     * @return resource
     */
    protected function openStream(): mixed
    {
        $path = $this->path;

        $stream = fopen($path, 'a') ?: throw new RuntimeException("Could not open stream {$path}");

        if ($this->filePermission !== null) {
            chmod($path, $this->filePermission);
        }

        return $this->stream = $stream;
    }
}
