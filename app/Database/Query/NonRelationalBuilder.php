<?php

namespace Kinko\Database\Query;

abstract class NonRelationalBuilder
{
    protected $connection;

    protected $collection;

    public function __construct($connection, $collection)
    {
        $this->connection = $connection;
        $this->collection = $collection;
    }

    /**
     * Add an "order by" clause to the query.
     *
     * @param  string  $field
     * @param  string  $direction
     * @return $this
     */
    abstract public function orderBy($field, $direction = 'asc');

    /**
     * Get an array with the values of a given field.
     *
     * @param  string  $field
     * @param  string|null  $key
     * @return \Illuminate\Support\Collection
     */
    abstract public function pluck($field, $key = null);

    /**
     * Execute an accumulation function on the database.
     *
     * @param  string  $field
     * @param  string  $operation
     * @return mixed
     */
    abstract public function accumulate($field, $operation);

    /**
     * Insert a new record into the database.
     *
     * @param  array  $values
     * @return bool
     */
    abstract public function insert(array $values);

    /**
     * Retrieve the minimum value of a given field.
     *
     * @param  string  $field
     * @return mixed
     */
    public function min($field)
    {
        return $this->accumulate($field, 'min');
    }

    /**
     * Retrieve the maximum value of a given field.
     *
     * @param  string  $field
     * @return mixed
     */
    public function max($field)
    {
        return $this->accumulate($field, 'max');
    }

    /* These should be removed (forced by usage of
     * Illuminate/Database/Migrations/DatabaseMigrationRepository class)
     **/

    public function useWritePdo()
    {
        return $this;
    }
}
