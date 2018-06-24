<?php

namespace Kinko\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Kinko\Database\MongoDB\MongoDB
 */
class MongoDB extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'mongodb';
    }
}
