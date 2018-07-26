<?php

namespace Kinko\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputObjectType;

class OrderBy extends InputObjectType
{
    const NAME = 'OrderBy';

    public function __construct()
    {
        parent::__construct([
            'name' => 'OrderBy',
            'fields' => [
                'AND' => [
                    'type' => Type::listOf(Type::nonNull($this)),
                ],
                'field' => ['type' => Type::string()],
                'direction' => [
                    'type' => new EnumType([
                        'name' => 'OrderByDirection',
                        'description' => 'Sort direction',
                        'values' => [
                            'ASC' => [
                                'value' => 'asc',
                                'description' => 'Sort in ascending order.'
                            ],
                            'ASCENDING' => [
                                'value' => 'asc',
                                'description' => 'Sort in ascending order.'
                            ],
                            'DESC' => [
                                'value' => 'desc',
                                'description' => 'Sort in descending order.'
                            ],
                            'DESCENDING' => [
                                'value' => 'desc',
                                'description' => 'Sort in descending order.'
                            ],
                        ]
                    ]),
                ],
            ],
        ]);
    }
}
