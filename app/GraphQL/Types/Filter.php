<?php

namespace Kinko\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputObjectType;

class Filter extends InputObjectType
{
    const NAME = 'Filter';

    public function __construct()
    {
        parent::__construct([
            'name' => 'Filter',
            'fields' => [
                'AND' => [
                    'type' => Type::listOf(Type::nonNull($this)),
                ],
                'OR' => [
                    'type' => Type::listOf(Type::nonNull($this)),
                ],
                'field' => ['type' => Type::string()],
                'operation' => [
                    'type' => new EnumType([
                        'name' => 'FilterOperation',
                        'description' => 'Matching operation',
                        'values' => [
                            'EQ' => [
                                'value' => '=',
                                'description' => 'Value is the same.'
                            ],
                            'EQUALS' => [
                                'value' => '=',
                                'description' => 'Value is the same.'
                            ],
                            'GT' => [
                                'value' => '>',
                                'description' => 'Value is greater.'
                            ],
                            'GREATER_THAN' => [
                                'value' => '>',
                                'description' => 'Value is greater.'
                            ],
                            'GTE' => [
                                'value' => '>=',
                                'description' => 'Value is greater or equal.'
                            ],
                            'GREATER_THAN_OR_EQUAL' => [
                                'value' => '>=',
                                'description' => 'Value is greater or equal.'
                            ],
                            'LT' => [
                                'value' => '<',
                                'description' => 'Value is less.'
                            ],
                            'LESS_THAN' => [
                                'value' => '<',
                                'description' => 'Value is less.'
                            ],
                            'LTE' => [
                                'value' => '<=',
                                'description' => 'Value is less or equal.'
                            ],
                            'LESS_THAN_OR_EQUAL' => [
                                'value' => '<=',
                                'description' => 'Value is less or equal.'
                            ],
                        ]
                    ]),
                ],
                'value' => ['type' => Type::string()], // TODO any
            ],
        ]);
    }
}
