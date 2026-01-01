<?php declare(strict_types=1);

namespace Kirameki\Framework\Exception;

use Kirameki\Exceptions\ErrorException;
use Kirameki\Framework\Exception\Reporters\Reporter;
use Throwable;
use function error_get_last;
use function register_shutdown_function;
use function set_error_handler;
use function set_exception_handler;
use const E_DEPRECATED;
use const E_USER_DEPRECATED;

class ExceptionHandler
{
    /**
     * @param list<Reporter> $reporters
     * @param list<Reporter> $deprecationReporters
     */
    public function __construct(
        protected array $reporters = [],
        protected array $deprecationReporters = [],
        protected ?Reporter $fallbackReporter = null,
    )
    {
        error_reporting(-1);
        $this->setExceptionHandling();
        $this->setErrorHandling();
        $this->setFatalHandling();
    }

    /**
     * @return void
     */
    protected function setExceptionHandling(): void
    {
        set_exception_handler($this->handleException(...));
    }

    /**
     * @return void
     */
    protected function setErrorHandling(): void
    {
        set_error_handler($this->handleError(...));
    }

    /**
     * @return void
     */
    protected function setFatalHandling(): void
    {
        register_shutdown_function(function() {
            if($e = error_get_last()) {
                $exception = new ErrorException($e['message'], 0, $e['type'], $e['file'], $e['line']);
                $this->handleException($exception);
            }
        });
    }

    /**
     * @param Throwable $exception
     * @return void
     */
    protected function handleException(Throwable $exception): void
    {
        $this->process($this->reporters, $exception);
    }

    /**
     * @param int $severity
     * @param string $message
     * @param string $file
     * @param int $line
     * @return bool
     */
    protected function handleError(int $severity, string $message, string $file, int $line): bool
    {
        $error = new ErrorException($message, $severity, $file, $line);

        if (in_array($severity, [E_DEPRECATED, E_USER_DEPRECATED])) {
            $this->process($this->deprecationReporters, $error);
            return true;
        }

        throw $error;
    }

    /**
     * @param list<Reporter> $reporters
     * @param Throwable $exception
     * @return void
     */
    protected function process(array $reporters, Throwable $exception): void
    {
        $caughtException = null;

        foreach ($reporters as $reporter) {
            try {
                $reporter->report($exception);
            }
            catch (Throwable $innerException) {
                $caughtException = $innerException;
            }
        }

        if ($caughtException !== null) {
            $this->fallback($caughtException);
        }
    }

    /**
     * @param Throwable $exception
     * @return void
     */
    protected function fallback(Throwable $exception): void
    {
        $this->fallbackReporter?->report($exception) ?? throw $exception;
    }
}
