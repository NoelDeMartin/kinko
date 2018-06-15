<?php

namespace Kinko\Database\Schema;

use Closure;
use Illuminate\Database\Schema\Builder;

abstract class NonRelationalBuilder extends Builder
{
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

    /**
     * Determine if the given collection exists.
     *
     * @param  string  $collection
     * @return bool
     */
    abstract public function hasCollection($collection);

    /**
     * Drop all collections from the database.
     *
     * @return void
     */
    abstract public function dropAllCollections();

    /* These should be removed (forced by usage of /Illuminate/Database/Schema/Builder class) */

    public function hasTable($table)
    {
        return $this->hasCollection($table);
    }

    public function dropAllTables()
    {
        return $this->dropAllCollections();
    }
}
