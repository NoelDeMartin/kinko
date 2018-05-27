<?php

namespace Kinko\Database\Schema;

use Closure;

abstract class NonRelationalBuilder
{
    protected $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Determine if the given collection exists.
     *
     * @param  string  $collection
     * @return bool
     */
    abstract public function hasCollection($collection);

    /**
     * Create a new command set.
     *
     * @param  string  $collection
     * @return \Kinko\Database\Schema\NonRelationalBlueprint
     */
    abstract protected function createBlueprint($collection);

    /**
     * Create a new collection on the schema.
     *
     * @param  string    $collection
     * @param  \Closure  $callback
     * @return void
     */
    public function create($collection, Closure $callback = null)
    {
        $blueprint = $this->createBlueprint($collection);

        if (!is_null($callback)) {
            $callback($blueprint);
        }

        $blueprint->apply($this->connection);
    }

    /* These should be removed (forced by usage of /Illuminate/Database/Schema/Builder class) */

    public function hasTable($table)
    {
        return $this->hasCollection($table);
    }
}
