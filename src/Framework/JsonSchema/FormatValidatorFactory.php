<?php declare(strict_types=1);

namespace Kirameki\Framework\JsonSchema;

use Kirameki\Exceptions\NotSupportedException;
use Kirameki\Framework\JsonSchema\Formats\DateTimeValidator;
use Kirameki\Framework\JsonSchema\Formats\DateValidator;
use Kirameki\Framework\JsonSchema\Formats\DurationValidator;
use Kirameki\Framework\JsonSchema\Formats\EmailValidator;
use Kirameki\Framework\JsonSchema\Formats\FormatValidator;
use Kirameki\Framework\JsonSchema\Formats\HostnameValidator;
use Kirameki\Framework\JsonSchema\Formats\Int32Validator;
use Kirameki\Framework\JsonSchema\Formats\Int64Validator;
use Kirameki\Framework\JsonSchema\Formats\TimeValidator;
use Kirameki\Framework\JsonSchema\Formats\UuidValidator;

class FormatValidatorFactory
{
    /**
     * @param array<string, FormatValidator> $validators
     */
    public function __construct(
        protected array $validators = []
    ) {
    }

    /**
     * @param string $format
     * @return FormatValidator
     */
    public function get(string $format): FormatValidator
    {
        return $this->validators[$format] ??= $this->resolve($format);
    }

    /**
     * @param string $format
     * @return FormatValidator
     */
    protected function resolve(string $format): FormatValidator
    {
        return match ($format) {
            // string
            DateTimeValidator::FORMAT => new DateTimeValidator(),
            DateValidator::FORMAT => new DateValidator(),
            DurationValidator::FORMAT => new DurationValidator(),
            EmailValidator::FORMAT => new EmailValidator(),
            HostnameValidator::FORMAT => new HostnameValidator(),
            TimeValidator::FORMAT => new TimeValidator(),
            UuidValidator::FORMAT => new UuidValidator(),
            // integer
            Int32Validator::FORMAT => new Int32Validator(),
            Int64Validator::FORMAT => new Int64Validator(),
            default => throw new NotSupportedException("Format '{$format}' is not supported.")
        };
    }
}
