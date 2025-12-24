<?php declare(strict_types=1);

namespace Kirameki\Framework\Logging\Handlers;

use Kirameki\Exceptions\RuntimeException;
use Kirameki\Framework\Logging\Formatters\Formatter;
use Kirameki\Framework\Logging\LogLevel;
use Kirameki\Framework\Logging\LogRecord;
use function chmod;
use function fclose;
use function flock;
use function fopen;
use function fwrite;
use function is_resource;
use const LOCK_EX;
use const LOCK_UN;

class StreamHandler extends Handler
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
    protected function write(LogRecord $record): void
    {
        $stream = $this->openStreamOnce();

        if ($this->useLocking) {
            flock($stream, LOCK_EX);
        }

        fwrite($stream, $this->formatter->format($record));

        if ($this->useLocking) {
            flock($stream, LOCK_UN);
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
        $stream = fopen($this->path, 'a') ?: throw new RuntimeException("Could not open stream {$this->path}");

        if ($this->filePermission !== null) {
            chmod($this->path, $this->filePermission);
        }

        return $this->stream = $stream;
    }
}
