<?php

namespace Kinko\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Kinko\Support\GraphQL
 */
class GraphQL extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'graphql';
    }
}
