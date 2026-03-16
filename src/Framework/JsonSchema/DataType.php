<?php declare(strict_types=1);

namespace Kirameki\Framework\JsonSchema;

enum DataType : string
{
    case String = 'string';
    case Number = 'number';
    case Integer = 'integer';
    case Boolean = 'boolean';
    case Null = 'null';
    case Object = 'object';
    case Array = 'array';
}
