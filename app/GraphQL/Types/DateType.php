<?php

namespace Kinko\GraphQL\Types;

use DateTime;
use GraphQL\Error\Error;
use Illuminate\Support\Carbon;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Language\AST\StringValueNode;

class DateType extends ScalarType
{
    public function serialize($value)
    {
        if (!($value instanceof DateTime)) {
            throw new Error('Invalid scalar Date given');
        }

        return $value->getTimestamp();
    }

    public function parseValue($value)
    {
        if ($value instanceof DateTime) {
            return $value;
        } else if (is_number($value)) {
            return Carbon::createFromTimestamp($value);
        } else if (is_string($value) && $value === 'now') {
            return Carbon::now();
        } else {
            throw new Error('Invalid value provided to parse Date');
        }
    }

    public function parseLiteral($valueNode, array $variables = null)
    {
        if ($valueNode instanceof IntValueNode) {
            return Carbon::createFromTimestamp($valueNode->value);
        } else if (($valueNode instanceof StringValueNode) && $valueNode->value === 'now') {
            return Carbon::now();
        } else {
            throw new Error('Query error: Can only parse integers or string "now", got: ' . $valueNode->kind, [$valueNode]);
        }
    }
}
