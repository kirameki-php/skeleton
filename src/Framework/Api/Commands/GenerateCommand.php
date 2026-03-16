<?php declare(strict_types=1);

namespace Kirameki\Framework\Api\Commands;

use Kirameki\Framework\Console\Command;
use Kirameki\Framework\Console\CommandBuilder;
use Kirameki\Framework\JsonSchema\JsonSchemaValidator;
use Kirameki\Process\ExitCode;
use Kirameki\Storage\Directory;
use Kirameki\Storage\File;
use Override;

class GenerateCommand extends Command
{
    /**
     * @inheritDoc
     */
    #[Override]
    public static function define(CommandBuilder $builder): void
    {
        $builder->name('api:generate');
    }

    public function __construct()
    {
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function run(): ?int
    {
        $dir = new Directory('oapi');
        $validator = new JsonSchemaValidator();
        foreach ($dir->scanRecursively()->all() as $file) {
            if ($file instanceof File && $file->extension === 'yaml') {
                $string = $file->read();
                $data = yaml_parse($string);
                $definition = JsonSchema;
                foreach ($data as $name => $value) {

                }

                $validator->validate($file);
            }
        }

        return ExitCode::SUCCESS;
    }
}
