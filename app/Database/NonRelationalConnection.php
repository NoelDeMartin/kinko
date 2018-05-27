<?php

namespace Kinko\Database;

use Illuminate\Database\ConnectionInterface;

abstract class NonRelationalConnection implements ConnectionInterface
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

    /**
     * Get a schema builder instance for the connection.
     *
     * @return \Kinko\Database\Schema\NonRelationalBuilder
     */
    abstract public function getSchemaBuilder();

    /**
     * Get the schema grammar used by the connection.
     *
     * @return \Kinko\Database\Schema\Grammars\NonRelationalGrammar
     */
    abstract public function getSchemaGrammar();

    /* These should be removed (forced by ConnectionInterface interface) */

    public function table($table)
    {
        return $this->collection($table);
    }
}
