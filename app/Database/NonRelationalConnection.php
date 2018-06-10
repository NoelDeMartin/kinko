<?php

namespace Kinko\Database;

use Illuminate\Database\Connection;

abstract class NonRelationalConnection extends Connection
{
    protected $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Begin a fluent query against a database collection.
     *
     * @param  string  $collection
     * @return \Kinko\Database\Schema\NonRelationalBuilder
     */
    abstract public function collection($collection);

    /* These should be removed (forced by ConnectionInterface interface) */

    public function table($table)
    {
        return $this->collection($table);
    }
}
