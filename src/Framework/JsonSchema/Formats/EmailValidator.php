<?php declare(strict_types=1);

namespace Kirameki\Framework\JsonSchema\Formats;

use Kirameki\Framework\JsonSchema\JsonSchema;
use Kirameki\Framework\JsonSchema\ValidationResultBuilder;
use Override;

class EmailValidator extends FormatValidator
{
    const string FORMAT = 'email';

    /**
     * @inheritDoc
     */
    #[Override]
    public function validate(JsonSchema $schema, object $data, array $path, ValidationResultBuilder $result): void
    {
        $value = $this->getValueFromPath($data, $path);

        // Validate email format (RFC 5321/5322)
        $pattern = '/^
            [a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+      # local part
            @                                       # at sign
            [a-zA-Z0-9]                             # domain start
            (?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?      # domain label
            (?:\.[a-zA-Z0-9]                        # dot-separated labels
                (?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?
            )+
        $/x';

        if (!preg_match($pattern, $value)) {
            $result->addError($path, "Value must be a valid email address, got: {$value}", ['got' => $value]);
        }
    }
}
