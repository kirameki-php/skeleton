<?php declare(strict_types=1);

namespace Kirameki\Framework\Exception;

use Kirameki\Container\Container;
use Kirameki\Framework\Exception\Reporters\Reporter;
use function is_string;

class ExceptionHandlerBuilder
{
    /**
     * @var list<Reporter>
     */
    protected array $reporters = [];

    /**
     * @var list<Reporter>
     */
    protected array $deprecatedReporters = [];

    /**
     * @var Reporter|null
     */
    protected ?Reporter $fallbackReporter = null;

    /**
     * @param Container $container
     */
    public function __construct(
        protected Container $container,
    ) {
    }

    /**
     * @param class-string<Reporter>|Reporter $reporter
     * @return $this
     */
    public function addReporter(string|Reporter $reporter): static
    {
        $this->reporters[] = $this->resolveReporter($reporter);
        return $this;
    }

    /**
     * @param class-string<Reporter>|Reporter $reporter
     * @return $this
     */
    public function addDeprecatedReporter(string|Reporter $reporter): static
    {
        $this->deprecatedReporters[] = $this->resolveReporter($reporter);
        return $this;
    }

    /**
     * @param class-string<Reporter>|Reporter $reporter
     * @return $this
     */
    public function setFallbackReporter(string|Reporter $reporter): static
    {
        $this->fallbackReporter = $this->resolveReporter($reporter);
        return $this;
    }

    /**
     * @param class-string<Reporter>|Reporter $reporter
     * @return Reporter
     */
    protected function resolveReporter(string|Reporter $reporter): Reporter
    {
        return is_string($reporter)
            ? $this->container->make($reporter)
            : $reporter;
    }

    /**
     * @return ExceptionHandler
     */
    public function build(): ExceptionHandler
    {
        return new ExceptionHandler(
            $this->reporters,
            $this->deprecatedReporters,
            $this->fallbackReporter,
        );
    }
}
